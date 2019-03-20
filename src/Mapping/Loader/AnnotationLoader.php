<?php

/*
 * Copyright (c) 2018-2019 Q.One Technologies GmbH, Essen
 * All rights reserved.
 *
 * This file is part of CloudBasket.
 *
 * NOTICE: The contents of this file are CONFIDENTIAL and MUST NOT be published
 * nor redistributed without prior written permission.
 */

namespace QOne\PrivacyBundle\Mapping\Loader;

use Doctrine\Common\Annotations\Reader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use QOne\PrivacyBundle\Annotation\Audited;
use QOne\PrivacyBundle\Annotation\Obsolesce;
use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadata;
use QOne\PrivacyBundle\Mapping\ObjectExpressionEvaluatorInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class AnnotationLoader.
 */
class AnnotationLoader implements LoaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ObjectExpressionEvaluatorInterface
     */
    protected $evaluator;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Finder|null
     */
    protected $finder;

    /**
     * AnnotationLoader constructor.
     *
     * @param ObjectExpressionEvaluatorInterface $evaluator
     * @param Reader                             $reader
     */
    public function __construct(ObjectExpressionEvaluatorInterface $evaluator, Reader $reader)
    {
        $this->evaluator = $evaluator;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function setFinder(Finder $finder): void
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadata(string $className, ClassMetadataInterface $classMetadata): bool
    {
        $reflectionClass = $classMetadata->getReflectionClass();
        $loaded = false;

        do {
            foreach ($this->reader->getClassAnnotations($reflectionClass) as $annotation) {
                switch (true) {
                    case $annotation instanceof Obsolesce:
                        if ($classMetadata->hasGroup($annotation->getGroup())) {
                            throw new PrivacyException(sprintf('Group "%s" already defined', $annotation->getGroup()));
                        }

                        $group = new GroupMetadata(
                            $annotation->getGroup(),
                            [],
                            $annotation->getConditions(),
                            $annotation->getPolicy(),
                            $annotation->getAuditTransformer(),
                            $annotation->getSource() ? $this->evaluator->parseExpression($annotation->getSource()) : null
                        );

                        $classMetadata->addGroup($group);
                        $loaded = true;
                        break;

                    case $annotation instanceof Audited:
                        $classMetadata->setUserExpr(
                            $this->evaluator->parseExpression($annotation->getUser())
                        );
                        $loaded = true;
                        break;
                }
            }

            foreach ($reflectionClass->getProperties() as $property) {
                if ($property->getDeclaringClass()->name === $className) {
                    foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                        if (!$annotation instanceof Obsolesce) {
                            continue;
                        }

                        $groupName = $annotation->getGroup();

                        if ($classMetadata->hasGroup($groupName)) {
                            if ($annotation->hasAttachments()) {
                                $parentGroup = $classMetadata->getGroup($groupName);
                                $groupName = sprintf('%s@%s', $groupName, $property->getName());

                                $group = new GroupMetadata(
                                    $groupName,
                                    [],
                                    $parentGroup->getConditions(),
                                    $parentGroup->getPolicy(),
                                    $parentGroup->getAuditTransformer(),
                                    $parentGroup->getSourceExpr()
                                );
                                $classMetadata->addGroup($group);
                            } else {
                                $group = $classMetadata->getGroup($groupName);
                            }
                        } else {
                            $group = new GroupMetadata(
                                $groupName,
                                [],
                                $annotation->getConditions(),
                                $annotation->getPolicy(),
                                $annotation->getAuditTransformer(),
                                $annotation->getSource()
                            );
                            $classMetadata->addGroup($group);
                        }

                        $group->addField($property->getName());
                        $loaded = true;
                    }
                }
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while (false !== $reflectionClass);

        return $loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadableClassNames(): array
    {
        if (null === $this->finder) {
            throw new \LogicException(sprintf('The \$finder needs to be defined prior to use %s', __METHOD__));
        }

        $classNames = [];
        $includedFiles = [];

        /** @var \SplFileInfo $file */
        foreach ($this->finder as $file) {
            $includedFiles[] = $filePath = $file->getRealPath();
            $this->logger->debug('AnnotationLoader loaded file {filePath}', compact('filePath'));
            require_once $filePath;
        }

        foreach (get_declared_classes() as $className) {
            try {
                $rc = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                // obviously, this class is not loadable
                continue;
            }

            $sourceFile = $rc->getFileName();

            if (!in_array($sourceFile, $includedFiles)
            || null === $this->reader->getClassAnnotation($rc, Audited::class)) {
                continue;
            }

            $classNames[] = $className;
        }

        return $classNames;
    }
}
