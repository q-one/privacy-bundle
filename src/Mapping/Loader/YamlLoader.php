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

use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadata;
use QOne\PrivacyBundle\Mapping\ObjectExpressionEvaluatorInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements LoaderInterface
{
    /**
     * @var ObjectExpressionEvaluatorInterface
     */
    protected $evaluator;

    /**
     * @var Finder|null
     */
    protected $finder;

    /**
     * YamlLoader constructor.
     *
     * @param ObjectExpressionEvaluatorInterface $evaluator
     * @param Finder|null                        $finder
     */
    public function __construct(ObjectExpressionEvaluatorInterface $evaluator, Finder $finder = null)
    {
        $this->evaluator = $evaluator;
        $this->setFinder($finder ?: new Finder());
    }

    /**
     * @param Finder $finder
     */
    public function setFinder(Finder $finder): void
    {
        $this->finder = $finder;
    }

    /**
     * @return Finder|null
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * @param string                 $className
     * @param ClassMetadataInterface $metadata
     *
     * @return bool
     *
     * @throws PrivacyException
     */
    public function loadMetadata(string $className, ClassMetadataInterface $metadata): bool
    {
        $yamlArray = $this->mergeYamlFiles();

        if (!isset($yamlArray[$className])) {
            return false;
            //throw new PrivacyException(sprintf('"%s" was not found in any of the yaml files', $className));
        }

        $classConfig = $yamlArray[$className];

        if ($user = $classConfig['user']) {
            $metadata->setUserExpr(
                $this->evaluator->parseExpression($user)
            );
        }

        $groupArray = $classConfig['groups'];

        $loaded = false;

        foreach ($groupArray as $groupName => $groupCfg) {
            $fieldArray = $groupCfg['fields'];
            $conditionArray = $groupCfg['conditions'];

            if (null === $fieldArray || 0 == count($fieldArray)) {
                throw new PrivacyException(sprintf('No fields specified for group "%s"', $groupName));
            }

            if (null === $conditionArray || 0 == count($conditionArray)) {
                throw new PrivacyException(sprintf('No conditions specified for group "%s"', $groupName));
            }

            if ($metadata->hasGroup($groupName)) {
                throw new PrivacyException(sprintf('Group "%s" already defined', $groupName));
            }

            $conditions = [];
            foreach ($conditionArray as $condition) {
                $conditionClass = $condition['condition'];
                $conditions[] = new $conditionClass($condition['properties']);
            }

            $group = new GroupMetadata(
                $groupName,
                $fieldArray,
                $conditions,
                $groupCfg['policy'] ?? null,
                $groupCfg['transformer'] ?? null,
                $groupCfg['source'] ? $this->evaluator->parseExpression($groupCfg['source']) : null
            );

            $metadata->addGroup($group);
            $loaded = true;
        }

        return $loaded;
    }

    /**
     * @return array
     */
    public function getLoadableClassNames(): array
    {
        $classNames = [];

        /** @var \SplFileInfo $file */
        foreach ($this->finder->files() as $file) {
            require_once $file->getRealPath();

            $array = Yaml::parseFile($file->getRealPath());
            array_fill_keys($classNames, array_keys($array));
        }

        return $classNames;
    }

    /**
     * @return array
     */
    private function mergeYamlFiles(): array
    {
        $array = [];

        /** @var \SplFileInfo $file */
        foreach ($this->finder->files() as $file) {
            $yamlArray = Yaml::parseFile($file->getRealPath());
            $array = array_merge($array, $yamlArray);
        }

        return $array;
    }
}
