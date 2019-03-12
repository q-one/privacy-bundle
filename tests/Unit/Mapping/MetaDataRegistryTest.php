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

namespace QOne\PrivacyBundle\Tests\Unit\Mapping;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Mapping\ClassMetadata;
use QOne\PrivacyBundle\Mapping\Loader\LoaderInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

class MetaDataRegistryTest extends TestCase
{
    /** @var TagAwareAdapterInterface|MockObject */
    protected $cache;

    /** @var LoaderInterface|MockObject */
    protected $loader;

    public function setUp()
    {
        parent::setUp();

        $this->cache = $this->createMock(TagAwareAdapterInterface::class);
        $this->loader = $this->createMock(LoaderInterface::class);
    }

    public function testConstructor()
    {
        $registry = $this->getMetaDataRegistry();

        $this->assertInstanceOf(MetadataRegistry::class, $registry);
    }

    public function testSetCache()
    {
        $registry = $this->getMetaDataRegistry();

        $registry->setCache($this->cache);
        $this->assertInstanceOf(MetadataRegistry::class, $registry);
    }

    public function testSetLoader()
    {
        $registry = $this->getMetaDataRegistry();

        $registry->setLoader($this->loader);
        $this->assertInstanceOf(MetadataRegistry::class, $registry);
    }

    public function testGetMetadataFor()
    {
        $this->expectException(\RuntimeException::class);
        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $cacheItem = new CacheItem();
        $metadata = new ClassMetadata('test');

        $cacheItem->set(json_encode($metadata));

        $this->cache->expects($this->once())->method('getItem')->willReturn($cacheItem);

        $registry->getMetadataFor('test');
    }

    /**
     * @throws \Exception
     */
    public function testGetMetadataForException()
    {
        $this->expectException(\RuntimeException::class);

        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $this->cache->expects($this->once())->method('getItem')->willThrowException(new InvalidArgumentException());

        $registry->getMetadataFor('test');
    }

    /**
     */
    public function testHasMetadataFor()
    {
        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $this->cache->expects($this->once())->method('hasItem')->willReturn(true);

        $result = $registry->hasMetadataFor('test');

        $this->assertTrue($result);
    }

    public function testHasMetadataForException()
    {
        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $this->cache->expects($this->once())->method('hasItem')->willThrowException(new InvalidArgumentException());

        $result = $registry->hasMetadataFor('test');

        $this->assertFalse($result);
    }

    public function testPruneMetadataFor()
    {
        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $this->cache->expects($this->once())->method('deleteItem');

        $registry->pruneMetadataFor('test');
    }

    public function testPruneMetadataForException()
    {
        $this->expectException(RuntimeException::class);

        $registry = $this->getMetaDataRegistry();
        $registry->setCache($this->cache);

        $this->cache->expects($this->once())->method('deleteItem')->willThrowException(new InvalidArgumentException());

        $registry->pruneMetadataFor('test');
    }


    private function getMetaDataRegistry()
    {
        return new MetadataRegistry($this->loader);
    }
}

class TestCachingClass
{
}
