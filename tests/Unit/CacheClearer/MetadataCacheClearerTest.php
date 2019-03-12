<?php

namespace QOne\PrivacyBundle\Tests\Unit\CacheClearer;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\CacheClearer\MetadataCacheClearer;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class MetadataCacheClearerTest extends TestCase
{
    /** @var TagAwareAdapterInterface|MockObject */
    protected $cache;

    public function setUp()
    {
        parent::setUp();

        $this->cache = $this->createMock(TagAwareAdapterInterface::class);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testClear()
    {
        $clearer = $this->getCacheClearer();
        $this->cache->expects($this->once())->method('invalidateTags');

        $clearer->clear('');
    }

    private function getCacheClearer()
    {
        return new MetadataCacheClearer($this->cache);
    }
}
