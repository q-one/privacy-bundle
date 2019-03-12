<?php

namespace QOne\PrivacyBundle\Tests\Unit\Command;


use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Command\ObsolescenceCommand;
use QOne\PrivacyBundle\Manager\PrivacyManagerInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObsolescenceCommandTest extends TestCase
{
    /**
     * @var PrivacyManagerInterface|MockObject
     */
    private $privacyManager;

    /**
     * @var MetadataRegistryInterface|MockObject
     */
    private $metadataRegistry;

    public function setUp()
    {
        parent::setUp();

        $this->privacyManager = $this->createMock(PrivacyManagerInterface::class);
        $this->metadataRegistry = $this->createMock(MetadataRegistryInterface::class);
    }

    public function testConstruct()
    {
        $command = new ObsolescenceCommand($this->privacyManager, $this->metadataRegistry, []);

        $this->assertInstanceOf(ObsolescenceCommand::class, $command);
    }

}
