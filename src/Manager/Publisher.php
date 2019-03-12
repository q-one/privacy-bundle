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

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use QOne\PrivacyBundle\Exception\NoSuchFieldException;
use QOne\PrivacyBundle\Exception\NoSuchObjectException;
use QOne\PrivacyBundle\Exception\TransformationException;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Survey\File;
use QOne\PrivacyBundle\Survey\FileInterface;
use QOne\PrivacyBundle\Survey\Format\EncodingInterface;
use QOne\PrivacyBundle\Survey\Group;
use QOne\PrivacyBundle\Survey\GroupInterface;
use QOne\PrivacyBundle\Survey\Record;
use QOne\PrivacyBundle\Survey\Survey;
use QOne\PrivacyBundle\Survey\SurveyInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Transformer\FieldTransformerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Publisher implements ContainerAwareInterface, LoggerAwareInterface, PublisherInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var PrivacyManagerInterface
     */
    private $privacyManager;

    /**
     * @var MetadataRegistryInterface
     */
    private $metadataRegistry;

    /**
     * @var EncodingInterface
     */
    private $encodingStrategy;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FieldTransformerInterface
     */
    private $defaultAuditTransformer;

    /**
     * Publisher constructor.
     *
     * @param PrivacyManagerInterface   $privacyManager
     * @param MetadataRegistryInterface $metadataRegistry
     * @param ManagerRegistry           $managerRegistry
     */
    public function __construct(PrivacyManagerInterface $privacyManager, MetadataRegistryInterface $metadataRegistry, ManagerRegistry $managerRegistry)
    {
        $this->privacyManager = $privacyManager;
        $this->metadataRegistry = $metadataRegistry;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultAuditTransformer(FieldTransformerInterface $defaultAuditTransformer): void
    {
        $this->defaultAuditTransformer = $defaultAuditTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAuditTransformer(): FieldTransformerInterface
    {
        return $this->defaultAuditTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingStrategy(EncodingInterface $encodingStrategy): void
    {
        $this->encodingStrategy = $encodingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodingStrategy(): EncodingInterface
    {
        return $this->encodingStrategy;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function createSurvey(SurveyRequest $request): SurveyInterface
    {
        $user = $request->createUserReference();
        $survey = new Survey($user);

        $assets = $this->privacyManager->getAssetsForUser($user);

        /** @var AssetInterface $asset */
        foreach ($assets as $asset) {
            try {
                $file = $this->processAsset($asset);
                $survey->getFiles()->add($file);
            } catch (NoSuchObjectException $e) {
                $this->logger->warning('Asset {id} for object class {class} can\'t be processed because it\'s not a registered entity', [
                    'id' => $asset->getId(),
                    'class' => $asset->getObject()->getClassName(),
                ]);
            }
        }

        return $survey;
    }

    protected function processAsset(AssetInterface $asset): FileInterface
    {
        $className = $asset->getObject()->getClassName();
        $classMetadata = $this->metadataRegistry->getMetadataFor($className);

        if (null === $om = $this->managerRegistry->getManagerForClass($className)) {
            throw new NoSuchObjectException(
                sprintf('Tried to get the Doctrine manager instance for %s, but this is no managed object.', $className)
            );
        }

        $object = $om->find($className, $asset->getObject()->getIdentifier());
        $groupMetadata = $classMetadata->getGroup($asset->getGroupId());
        $group = $this->processGroup($object, $groupMetadata, $asset);

        return new File($asset->getObject(), $asset->getSource(), [$group]);
    }

    protected function processGroup(object $object, GroupMetadataInterface $groupMetadata, AssetInterface $asset): GroupInterface
    {
        $auditTransformer = $this->defaultAuditTransformer;

        if (null !== $auditTransformerService = $groupMetadata->getAuditTransformer()) {
            /** @var FieldTransformerInterface $auditTransformer */
            $auditTransformer = $this->container->get($auditTransformerService, ContainerInterface::NULL_ON_INVALID_REFERENCE);

            if (!$auditTransformer instanceof FieldTransformerInterface) {
                throw new TransformationException(
                    sprintf(
                        'Expected %s to be a valid audit transformer for %s (group %s), but got %s',
                        $auditTransformerService, get_class($object), $groupMetadata->getId(), get_class($auditTransformer)
                    )
                );
            }
        }

        $records = array_map(function ($fieldName) use ($object, $auditTransformer) {
            return new Record($fieldName, $this->getFieldValue($object, $fieldName, $auditTransformer));
        }, $groupMetadata->getFields());

        return new Group($groupMetadata->getId(), $groupMetadata->getConditions(), $records, $asset->getSource());
    }

    /**
     * @param object                    $object
     * @param string                    $fieldName
     * @param FieldTransformerInterface $auditTransformer
     *
     * @return mixed
     *
     * @throws NoSuchFieldException
     * @throws TransformationException
     */
    protected function getFieldValue(object $object, string $fieldName, FieldTransformerInterface $auditTransformer)
    {
        try {
            $refClass = new \ReflectionClass($object);

            $property = $refClass->getProperty($fieldName);
            $property->setAccessible(true);
            $fieldValue = $property->getValue($object);
        } catch (\ReflectionException $e) {
            throw new NoSuchFieldException(
                sprintf('Tried to fetch %s::%s but no such field!', get_class($object), $fieldName),
                0, $e
            );
        }

        try {
            return $auditTransformer->transformFieldValue($object, $fieldName, $fieldValue);
        } catch (\Throwable $e) {
            throw new TransformationException(
                sprintf('Failed to audit-transform field %s::%s', get_class($object), $fieldName),
                0, $e
            );
        }
    }
}
