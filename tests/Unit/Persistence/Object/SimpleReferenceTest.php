<?php

namespace QOne\PrivacyBundle\Tests\Unit\Persistence\Object;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\SimpleReference;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class SimpleReferenceTest extends TestCase
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $identifier;

    /**
     * @var ObjectReferenceInterface|MockObject
     */
    private $reference;

    /**
     * @var ManagerRegistry|MockObject
     */
    private $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->className = \stdClass::class;
        $this->identifier = [];
        $this->reference = $this->createMock(ObjectReferenceInterface::class);
        $this->manager = $this->createMock(ManagerRegistry::class);
    }

    public function testEquals()
    {
        $reference = $this->getSimpleReference();

        $this->assertFalse($reference->equals($this->reference));
    }

    public function testFromObject()
    {
        $this->expectException(\BadMethodCallException::class);

        $reference = $this->getSimpleReference();

        $reference->fromObject($this->manager, new \stdClass());
    }

    public function testToString()
    {
        $reference = $this->getSimpleReference();

        $this->assertSame('stdClass({})', (string)$reference);
    }

    public function getSimpleReference(): SimpleReference
    {
        return new SimpleReference($this->className, $this->identifier);
    }
}
