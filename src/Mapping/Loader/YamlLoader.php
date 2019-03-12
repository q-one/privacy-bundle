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

namespace QOne\PrivacyBundle\Mapping\Loader;

use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadata;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements LoaderInterface
{
    /**
     * @var Finder|null
     */
    protected $finder;

    /**
     * YamlLoader constructor.
     *
     * @param Finder|null $finder
     */
    public function __construct(Finder $finder = null)
    {
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
            throw new PrivacyException(sprintf('"%s" was not found in any of the yaml files', $className));
        }

        $classConfig = $yamlArray[$className];

        if ($user = $classConfig['user']) {
            $metadata->setUserExpr($user);
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
                $groupCfg['source'] ?? null
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
