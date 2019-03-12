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

use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use Symfony\Component\Finder\Finder;

class ChainedLoader implements LoaderInterface
{
    /** @var array */
    protected $loaders;

    public function __construct(array $loaders = [])
    {
        $this->addLoaders($loaders);
    }

    /**
     * @param array $loaders
     */
    public function addLoaders(array $loaders) {
        foreach ($loaders as $loader) {
            if (!$loader instanceof LoaderInterface) {
                throw new \InvalidArgumentException(sprintf('Expected $loaders to be an array of LoaderInterface, but got an element of %s', get_class($loader)));
            }

            $this->loaders[] = $loader;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFinder(Finder $finder): void
    {
        foreach ($this->loaders as $loader) {
            /* @var LoaderInterface $loader */
            $loader->setFinder($finder);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadata(string $className, ClassMetadataInterface $metadata): bool
    {
        $success = false;

        foreach ($this->loaders as $loader) {
            /** @var LoaderInterface $loader */
            $success = $loader->loadMetadata($className, $metadata) || $success;
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadableClassNames(): array
    {
        $classNames = array_map(function ($loader) {
            /* @var LoaderInterface $loader */
            return $loader->getLoadableClassNames();
        }, $this->loaders);

        return array_reduce($classNames, function ($c, $v) {
            return array_unique(array_merge($c, $v));
        }, []);
    }
}
