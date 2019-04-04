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

namespace QOne\PrivacyBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Condition\EvaluatorInterface;
use QOne\PrivacyBundle\Condition\PredictingEvaluatorInterface;
use QOne\PrivacyBundle\Exception\InvalidMetadataException;
use QOne\PrivacyBundle\Exception\LogicException;
use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Obsolescence\LegacyResult;
use QOne\PrivacyBundle\Obsolescence\ObsolescenceResult;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\PersistenceManagerInterface;
use QOne\PrivacyBundle\Persistence\Repository\AssetRepositoryInterface;
use QOne\PrivacyBundle\Persistence\Repository\LegacyRepositoryInterface;
use QOne\PrivacyBundle\Persistence\Repository\StateRepositoryInterface;
use QOne\PrivacyBundle\Policy\PolicyInterface;
use QOne\PrivacyBundle\Verdict\PrejudgingVerdict;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main component of the QOnePrivacyBundle that manages the obsoletion.
 */
class PrivacyManager implements ContainerAwareInterface, LoggerAwareInterface, PrivacyManagerInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var MetadataRegistryInterface
     */
    protected $metadataRegistry;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     */
    protected $legacyManagerName;

    /**
     * @var LegacyRepositoryInterface
     */
    protected $legacyRepository;

    /**
     * @var \DateInterval|null
     */
    protected $reevaluationInterval;

    /**
     * PrivacyManager constructor.
     *
     * @param MetadataRegistryInterface   $metadataRegistry
     * @param ManagerRegistry             $registry
     * @param PersistenceManagerInterface $persistenceManager
     * @param string                      $legacyManagerName
     */
    public function __construct(
        MetadataRegistryInterface $metadataRegistry,
        ManagerRegistry $registry,
        PersistenceManagerInterface $persistenceManager,
        string $legacyManagerName
    ) {
        $this->metadataRegistry = $metadataRegistry;
        $this->registry = $registry;
        $this->persistenceManager = $persistenceManager;
        $this->legacyManagerName = $legacyManagerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsForUser(ObjectReferenceInterface $user): Collection
    {
        $assets = [];

        /** @var ObjectManager $om */
        foreach ($this->registry->getManagers() as $om) {
            $isManaged = false;

            try {
                $om->getMetadataFactory()->getMetadataFor(AssetInterface::class);
                $isManaged = true;
            } catch(MappingException $e)  {
                // Do nothing!
            }

            if ($isManaged) {
                /** @var AssetRepositoryInterface $repo */
                $repo = $om->getRepository(AssetInterface::class);
                $assets = array_merge($assets, $repo->findAssetsForUser($user));
            }
        }

        return new ArrayCollection($assets);
    }

    /**
     * {@inheritdoc}
     */
    public function doObsolescence(string $objectManagerName): ObsolescenceResult
    {
        $om = $this->getObjectManager($objectManagerName);

        return $this->persistenceManager->transactional($om, function ($om) {
            /* @var ObjectManager $om */
            /* @var AssetRepositoryInterface $assetRepository */
            /* @var StateRepositoryInterface $stateRepository */

            $assetRepository = $om->getRepository(AssetInterface::class);
            $stateRepository = $om->getRepository(StateInterface::class);

            // lock the current position
            $stateRepository->getIntValue(StateInterface::KEY_LEGACY_POSITION);

            if (null === $asset = $assetRepository->findNextValidationAsset($this->reevaluationInterval)) {
                $this->logger->debug('No further asset left to process');

                return new ObsolescenceResult(ObsolescenceResult::EEOF, null, null, null);
            }

            $assetId = $asset->getId();
            $objectReference = $asset->getObject();
            $objectClassName = $objectReference->getClassName();
            $classMetadata = $this->metadataRegistry->getMetadataFor($objectClassName);

            $object = self::getObjectFromReference($om, $objectReference);
            if (null === $object) {
                $this->logger->error('Can\'t find an Object for the given class {class} and identifier {identifier}.', [
                    'class' => $objectClassName,
                    'identifier' => 'foo',
                ]);

                $om->remove($asset);

                return new ObsolescenceResult(ObsolescenceResult::ENOSUCHCLASS, $asset, null, null);
            }

            /* @var GroupMetadataInterface $groupMetadata */
            $groupMetadata = $classMetadata->getGroup($asset->getGroupId());
            $verdict = $this->createVerdict($object, $classMetadata, $groupMetadata);
            $prediction = null;

            foreach ($groupMetadata->getConditions() as $condition) {
                /** @var ConditionInterface $condition */
                $evaluator = $this->createEvaluator($condition);
                $judge = $evaluator->evaluate($object, $objectReference, $condition);

                $this->logger->debug(
                    'In the context of asset {assetId} condition {condition} has been evaluated for object {objectReference} by {evaluator} => {judge}',
                    compact('assetId', 'condition', 'evaluator', 'judge')
                );

                if ($evaluator instanceof PredictingEvaluatorInterface) {
                    $prediction = max(
                        $prediction,
                        $evaluator->predict($object, $objectReference, $condition)
                    );
                }

                $verdict->addVote($condition, $judge);
            }

            $this->logger->debug(
                'Verdict for asset {assetId} is {res} with {appropriate} votes for appropriate, {keep} for keep, and {obsolete} for obsolete; prediction is {prediction}',
                compact('assetId') + [
                    'res' => $verdict->isObsolete() ? 'OBSOLETE' : 'KEEP',
                    'appropriate' => count($verdict->getAppropriateVotes()),
                    'keep' => count($verdict->getKeepVotes()),
                    'obsolete' => count($verdict->getObsoleteVotes()),
                    'prediction' => $prediction ?: 'NULL',
                ]
            );

            if ($verdict->isObsolete()) {
                $legacy = $this->getLegacyRepository()->leaveLegacy($asset, $groupMetadata);
                $legacyId = $legacy->getId();

                $stateRepository->setIntValue(StateInterface::KEY_LEGACY_POSITION, $legacyId);
                $this->createPolicy($groupMetadata)->apply($verdict);
                $om->remove($asset);

                $policyService = $groupMetadata->getPolicy();
                $this->logger->notice(
                    'Asset {assetId} caused object {objectReference} to be applied with policy {policyService}; related legacy is {legacyId}',
                    compact('assetId', 'legacyId', 'objectReference', 'policyService')
                );

                return new ObsolescenceResult(ObsolescenceResult::ESUCCESS, $asset, $legacy, $prediction);
            }

            $asset->updateEvaluationValidatedAt();
            $asset->updateEvaluationPredictedAt($prediction);
            $om->persist($asset);

            $this->logger->debug(
                'Asset {assetId} of object {objectReference} has been evaluated without obsoletion; prediction is {prediction}',
                compact('assetId', 'objectReference') + [
                    'prediction' => $prediction ?: 'NULL',
                ]
            );

            return new ObsolescenceResult(ObsolescenceResult::ESUCCESS, $asset, null, $prediction);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function doLegacy(string $objectManagerName): LegacyResult
    {
        $om = $this->getObjectManager($objectManagerName);

        return $this->persistenceManager->transactional($om, function ($om) {
            /* @var ObjectManager $om */
            /* @var LegacyRepositoryInterface $legacyRepository */
            /* @var StateRepositoryInterface $stateRepository */

            $legacyRepository = $this->getLegacyRepository();
            $stateRepository = $om->getRepository(StateInterface::class);

            // get the current position
            $currentPosition = $stateRepository->getIntValue(StateInterface::KEY_LEGACY_POSITION) ?? 0;

            if (null === $legacy = $legacyRepository->findNextLegacy($currentPosition)) {
                $this->logger->debug('No further legacy left to process');

                return new LegacyResult(LegacyResult::EEOF, null, $currentPosition);
            }

            $legacyId = $legacy->getId();
            $stateRepository->setIntValue(StateInterface::KEY_LEGACY_POSITION, $legacyId);

            $objectReference = $legacy->getObject();
            $objectClassName = $objectReference->getClassName();

            if (null === $om->getClassMetadata($objectClassName)) {
                $this->logger->info(
                    'Skipping legacy {legacyId} of class {objectClassName}, as this class is not transient in current object manager.',
                    compact('legacyId', 'objectClassName')
                );

                return new LegacyResult(LegacyResult::ENOSUCHCLASS, $legacy, $currentPosition);
            }

            $classMetadata = $this->metadataRegistry->getMetadataFor($objectClassName);
            $groupMetadata = $legacy->restoreGroupMetadata();
            $policyService = $groupMetadata->getPolicy();

            $object = self::getObjectFromReference($om, $objectReference);
            $verdict = new PrejudgingVerdict($object, $classMetadata, $groupMetadata);

            $this->createPolicy($groupMetadata)->apply($verdict);
            $this->logger->notice(
                'Legacy {legacyId} caused object {objectReference} to be applied with policy {policyService} again',
                compact('legacyId', 'objectReference', 'policyService')
            );

            return new LegacyResult(LegacyResult::ESUCCESS, $legacy, $currentPosition);
        });
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function setReevaluationInterval($reevaluationInterval): void
    {
        if (is_string($reevaluationInterval)) {
            $reevaluationInterval = new \DateInterval($reevaluationInterval);
        }

        if (null !== $reevaluationInterval && !$reevaluationInterval instanceof \DateInterval) {
            $type = gettype($reevaluationInterval);
            $type = ('object' === $type) ? get_class($type) : $type;
            throw new \InvalidArgumentException(sprintf('Expected $reevaluationInterval to be either a DateInterval or a compatible string, but got %s', $type));
        }

        $this->reevaluationInterval = $reevaluationInterval;
    }

    /**
     * @param ConditionInterface $condition
     *
     * @return EvaluatorInterface
     *
     * @throws LogicException
     */
    protected function createEvaluator(ConditionInterface $condition): EvaluatorInterface
    {
        $evaluationService = $condition->evaluatedBy();

        /** @var EvaluatorInterface|null $evaluator */
        $evaluator = $this->container->get($evaluationService, ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if (!$evaluator instanceof EvaluatorInterface) {
            throw new LogicException(sprintf(
                'Expected given evaluation service "%s" for Condition of class %s to be a valid instance of EvaluatorInterface, but got %s!',
                $evaluationService,
                ClassUtils::getClass($condition),
                null === $evaluator ? 'NULL' : get_class($evaluator)
            ));
        }

        return $evaluator;
    }

    /**
     * @param GroupMetadataInterface $groupMetadata
     *
     * @return PolicyInterface
     *
     * @throws PrivacyException
     */
    protected function createPolicy(GroupMetadataInterface $groupMetadata): PolicyInterface
    {
        $policyService = $groupMetadata->getPolicy()
            ?? $this->container->getParameter('qone_privacy.obsolescence.default_policy');

        /** @var PolicyInterface|null $policy */
        $policy = $this->container->get($policyService, ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if (!$policy instanceof PolicyInterface) {
            throw new InvalidMetadataException(sprintf(
                'Expected given policy service "%s" for group %s to be a valid instance of PolicyInterface, but got %s!',
                $policyService,
                $groupMetadata->getId(),
                null === $policy ? 'NULL' : get_class($policy)
            ));
        }

        return $policy;
    }

    /**
     * @param object                 $object
     * @param ClassMetadataInterface $class
     * @param GroupMetadataInterface $group
     *
     * @return VerdictInterface
     *
     * @throws PrivacyException
     */
    protected function createVerdict(object $object, ClassMetadataInterface $class, GroupMetadataInterface $group): VerdictInterface
    {
        $verdictArchetype = $this->container->getParameter('qone_privacy.obsolescence.default_verdict');

        if (!is_a($verdictArchetype, VerdictInterface::class, true)) {
            throw new InvalidMetadataException(sprintf(
                'Expected configured default verdict "%s" to be an instance of VerdictInterface',
                $verdictArchetype
            ));
        }

        return $verdictArchetype::create($object, $class, $group);
    }

    /**
     * @return LegacyRepositoryInterface
     */
    protected function getLegacyRepository()
    {
        if (null === $this->legacyRepository) {
            $this->legacyRepository = $this->registry
                ->getManager($this->legacyManagerName)
                ->getRepository(LegacyInterface::class)
            ;
        }

        return $this->legacyRepository;
    }

    /**
     * @param ObjectManager            $om
     * @param ObjectReferenceInterface $objectReference
     *
     * @return object|null
     */
    protected static function getObjectFromReference(ObjectManager $om, ObjectReferenceInterface $objectReference): ? object
    {
        $object = $om
            ->getRepository($objectReference->getClassName())
            ->find($objectReference->getIdentifier())
        ;

        return $object;
    }

    /**
     * @param string $name
     *
     * @return ObjectManager
     */
    protected function getObjectManager(string $name): ObjectManager
    {
        $om = $this->registry->getManager($name);

        $isManaged = false;
        try {
            $om->getMetadataFactory()->getMetadataFor(AssetInterface::class);
            $isManaged = true;
        } catch (MappingException $e) {
            // Do nothing!
        }

        if (!$isManaged) {
            throw new \InvalidArgumentException(sprintf('Given object manager "%s" is not configured to manage privacy-audited entities', $name));
        }

        return $om;
    }
}
