<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
 * This file is part of QOnePrivacyBundle.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace QOne\PrivacyBundle\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use QOne\PrivacyBundle\Exception\PersistenceException;
use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Mapping\MetadataRegistry;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Mapping\ObjectExpressionEvaluatorInterface;
use QOne\PrivacyBundle\ORM\Entity\Asset;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Repository\AssetRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This listener is responsible for maintaining the Asset's only in a Doctrine ORM context.
 */
class PrivacyORMListener implements EventSubscriber, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MetadataRegistry
     */
    protected $metadataRegistry;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var ObjectExpressionEvaluatorInterface
     */
    protected $expressionEvaluator;

    /**
     * @var array
     */
    private $preUpdateState = [];

    /**
     * @var array
     */
    private $preRemoveState = [];

    /**
     * This "cache" holds references to the last inserted entities
     * until the entity manager gets cleared.
     *
     * As the spl_object_hash() may be reused for new objects the entity
     * manager mistakes obviously new Asset's for already managed ones
     * otherwise.
     *
     * @var array
     */
    private $insertionState = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
            Events::postFlush,
            Events::onClear,
        ];
    }

    /**
     * PrivacyORMListener constructor.
     *
     * @param MetadataRegistryInterface          $metadataRegistry
     * @param ManagerRegistry                    $managerRegistry
     * @param ObjectExpressionEvaluatorInterface $evaluator
     */
    public function __construct(
        MetadataRegistryInterface $metadataRegistry,
        ManagerRegistry $managerRegistry,
        ObjectExpressionEvaluatorInterface $evaluator
    ) {
        $this->metadataRegistry = $metadataRegistry;
        $this->managerRegistry = $managerRegistry;
        $this->expressionEvaluator = $evaluator;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @throws PrivacyException
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $doctrineMetadata = $eventArgs->getClassMetadata();
        $className = $doctrineMetadata->getName();

        if (!$this->metadataRegistry->hasMetadataFor($className)) {
            return;
        }

        $metadata = $this->metadataRegistry->getMetadataFor($className);

        foreach ($metadata->getGroups() as $group) {
            foreach ($group->getFields() as $fieldName) {
                switch (true) {
                    case $doctrineMetadata->hasField($fieldName):
                        $fieldMapping = $doctrineMetadata->getFieldMapping($fieldName);

                        if (true !== $fieldMapping['nullable']) {
                            throw new PersistenceException(sprintf('Every privacy-related field must be nullable, but field "%s" of class %s is not', $fieldName, $className));
                        }
                        break;

                    case $doctrineMetadata->hasAssociation($fieldName):
                        if (!$doctrineMetadata->isSingleValuedAssociation($fieldName)) {
                            throw new PersistenceException(sprintf('Privacy-related field "%s" of class %s mustn\'t be a multi-valued association', $fieldName, $className));
                        }

                        if (!$doctrineMetadata->isAssociationWithSingleJoinColumn($fieldName)) {
                            throw new PersistenceException(sprintf('QOnePrivacyBundle doesn\'t support that field "%s" of class %s has multiple join columns yet.', $fieldName, $className));
                        }

                        if (!($doctrineMetadata->getAssociationMapping($fieldName)['isOwningSide'] ?? false)) {
                            throw new PersistenceException(sprintf('Privacy-related field "%s" of class %s must be owning side.', $fieldName, $className));
                        }
                        break;

                    default:
                        throw new PersistenceException(sprintf(
                            'Privacy-audited field "%s" of class %s is not a Doctrine ORM property',
                            $fieldName, $className
                        ));
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return;
        }

        $user = $this->expressionEvaluator->getAttachedUser($entity);
        $metadata = $this->metadataRegistry->getMetadataFor($entityClass);

        /** @var EntityReference $userReference */
        /** @var ObservedEntityReference $entityReference */
        $userReference = $this->createUserReference($user);
        $entityReference = $this->createEntityReference($entity);

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $persister = $uow->getEntityPersister(Asset::class);

        if (count($persister->getInserts()) > 0) {
            throw new \LogicException(sprintf('Expected persister of %s to be empty', Asset::class));
        }

        foreach ($metadata->getGroups() as $group) {
            $source = $this->expressionEvaluator->getAttachedSource($entity, $group->getId());
            $asset = &$this->insertionState[];
            $asset = new Asset($userReference, $entityReference, $group->getId(), $source);

            $uow->computeChangeSet($em->getClassMetadata(Asset::class), $asset);
            $persister->addInsert($asset);
        }

        $persister->executeInserts();
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return;
        }

        $entityReference = $this->createEntityReference($entity);
        $metadata = $this->metadataRegistry->getMetadataFor($entityClass);

        $em = $eventArgs->getEntityManager();

        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $em->getRepository(AssetInterface::class);

        $oid = spl_object_hash($entity);
        $changedFields = array_keys($eventArgs->getEntityChangeSet());
        $this->preUpdateState[$oid] = [];

        foreach ($metadata->getGroups() as $group) {
            $groupId = $group->getId();
            $groupFields = $group->getFields();

            $asset = $assetRepository->findAssetForObjectGroup($entityReference, $groupId);

            /*
             * We need to determine if the set of fields of this group intersects with the set
             * of changed fields, e.g. the corresponding asset will only be updated if a field
             * of the group has been changed in the current ORM update operation.
             */
            $updateScheduled = count(array_intersect($groupFields, $changedFields)) > 0;
            $this->preUpdateState[$oid][$groupId] = compact('asset', 'updateScheduled');
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return;
        }

        $user = $this->expressionEvaluator->getAttachedUser($entity);
        /** @var EntityReference $userReference */
        $userReference = $this->createUserReference($user);
        $metadata = $this->metadataRegistry->getMetadataFor($entityClass);

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $oid = spl_object_hash($entity);
        $persister = $uow->getEntityPersister(Asset::class);

        foreach ($metadata->getGroups() as $group) {
            $groupId = $group->getId();
            $groupPreUpdate = &$this->preUpdateState[$oid][$groupId];
            $source = $this->expressionEvaluator->getAttachedSource($entity, $groupId);

            if (null === $groupPreUpdate['asset']) {
                /** @var ObservedEntityReference $entityReference */
                $entityReference = $this->createEntityReference($entity);

                $this->logger->info(
                    'Missing group "{groupId}" while updating object {entityReference} (oid={oid}) of user {userReference}; adding such group as an asset',
                    compact('entityReference', 'userReference', 'groupId', 'oid')
                );

                $asset = &$this->insertionState[];
                $asset = new Asset($userReference, $entityReference, $groupId, $source);

                $uow->computeChangeSet($em->getClassMetadata(Asset::class), $asset);
                $persister->addInsert($asset);
                $persister->executeInserts();

                $groupPreUpdate['asset'] = $asset;
                $groupPreUpdate['updateScheduled'] = false;
            }

            /** @var Asset $asset */
            $asset = $groupPreUpdate['asset'];

            if (!$groupPreUpdate['updateScheduled'] && $userReference->equals($asset->getUser())) {
                continue;
            }

            $asset->setUser($userReference);

            if ($groupPreUpdate['updateScheduled']) {
                $asset->setSource($source);
                $asset->getObject()->refreshUpdatedAt();
            }

            $uow->computeChangeSet($em->getClassMetadata(Asset::class), $asset);
            $persister->update($asset);
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return;
        }

        $entityReference = $this->createEntityReference($entity);
        $oid = spl_object_hash($entity);

        /*
         * Unfortunately, in contrary to the preUpdate event, the preRemove event in
         * fact is *not* part of the transaction. We can only store the entity reference
         * here and have to fetch and delete the asset in postRemove. Yuck!
         * This does not prevent the entirely possible race condition when the identifier of the
         * referenced entity changes between preRemove and the start of the removal transaction.
         *
         * However ... this critical as the worst case is a superfluous Asset in the database
         * that will be cleaned up automatically with the next re-validation, and which can happen
         * anyway if a whole collection gets deleted.
         */
        $this->preRemoveState[$oid] = compact('entityReference');
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return;
        }

        $user = $this->expressionEvaluator->getAttachedUser($entity);
        $userReference = $this->createUserReference($user);
        $metadata = $this->metadataRegistry->getMetadataFor($entityClass);

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $em->getRepository(AssetInterface::class);

        $oid = spl_object_hash($entity);
        $prs = &$this->preRemoveState[$oid];

        $persister = $uow->getEntityPersister(Asset::class);

        foreach ($metadata->getGroups() as $group) {
            $groupId = $group->getId();

            if (null === $asset = $assetRepository->findAsset($userReference, $prs['entityReference'], $groupId)) {
                /** @var ObservedEntityReference $entityReference */
                $entityReference = $this->createEntityReference($entity);

                $this->logger->info(
                    'Missing asset for group "{groupId}" while removing object {entityReference} (oid={oid}) of user {userReference}; just continuing.',
                    compact('entityReference', 'userReference', 'groupId', 'oid')
                );

                continue;
            }

            $persister->delete($asset);
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(/* @noinspection PhpUnusedParameterInspection */ PostFlushEventArgs $eventArgs): void
    {
        $this->resetPreviousState();
    }

    /**
     * @param OnClearEventArgs $eventArgs
     */
    public function onClear(/* @noinspection PhpUnusedParameterInspection */ OnClearEventArgs $eventArgs): void
    {
        $this->resetPreviousState();
        $this->resetInsertionState();
    }

    private function resetPreviousState(): void
    {
        $this->preUpdateState = $this->preRemoveState = [];
    }

    private function resetInsertionState(): void
    {
        $this->insertionState = [];
    }

    private function createUserReference(UserInterface $user)
    {
        return EntityReference::fromObject($this->managerRegistry, $user);
    }

    private function createEntityReference(object $entity)
    {
        return ObservedEntityReference::fromObject($this->managerRegistry, $entity);
    }
}
