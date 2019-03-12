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

namespace QOne\PrivacyBundle\Mapping;

use QOne\PrivacyBundle\Mapping\Loader\LoaderInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * The MetadataRegistry holds and manages the information about the ClassMetadata regarding
 * privacy settings on persistence objects.
 */
interface MetadataRegistryInterface
{
    /**
     * @param TagAwareAdapterInterface|null $cache
     */
    public function setCache(?TagAwareAdapterInterface $cache): void;

    /**
     * @param LoaderInterface $loader
     */
    public function setLoader(LoaderInterface $loader): void;

    /**
     * @param string $className
     *
     * @return ClassMetadataInterface
     */
    public function getMetadataFor(string $className): ClassMetadataInterface;

    /**
     * @param string $className
     *
     * @return bool
     */
    public function hasMetadataFor(string $className): bool;

    /**
     * @param string $className
     */
    public function loadMetadataFor(string $className): void;

    /**
     * @param string $className
     */
    public function pruneMetadataFor(string $className): void;

    /**
     * @return array
     */
    public function getLoadableClassNames(): array;
}
