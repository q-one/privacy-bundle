<?php

namespace QOne\PrivacyBundle\Tests\Unit\CacheWarmer;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\CacheWarmer\MetadataCacheWarmer;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MetadataCacheWarmerTest extends TestCase
{
    /** @var MetadataRegistryInterface|MockObject */
    public $metaDataRegistry;

    public function setUp()
    {
        parent::setUp();

        $this->metaDataRegistry = $this->createMock(MetadataRegistryInterface::class);
    }

    public function testIsOptional()
    {
        $warmer = $this->getCacheWarmer();

        $this->assertFalse($warmer->isOptional());
    }

    public function testWarmUp()
    {
        $this->metaDataRegistry->expects($this->once())->method('getLoadableClassNames')->willReturn(['foo']);

        $warmer = $this->getCacheWarmer();

        $this->assertNull($warmer->warmUp('/'));

    }

    private function getCacheWarmer()
    {
        return new MetadataCacheWarmer($this->metaDataRegistry);
    }
}
