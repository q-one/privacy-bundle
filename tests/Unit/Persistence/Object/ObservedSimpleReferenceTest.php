<?php

namespace QOne\PrivacyBundle\Tests\Unit\Persistence\Object;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\ObservedSimpleReference;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObservedSimpleReferenceTest extends TestCase
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

    public function testGetClassName()
    {
        $observed = $this->getObservedSimpleReference();
        $this->assertSame($this->className, $observed->getClassName());
    }

    public function testGetIdentifier()
    {
        $observed = $this->getObservedSimpleReference();
        $this->assertSame($this->identifier, $observed->getIdentifier());
    }

    public function testEquals()
    {
        $reference = $this->getObservedSimpleReference();

        $this->assertFalse($reference->equals($this->reference));
    }

    public function testFromObject()
    {
        $this->expectException(\BadMethodCallException::class);

        $reference = $this->getObservedSimpleReference();

        $reference->fromObject($this->manager, new \stdClass());
    }

    /**
     * @throws \Exception
     */
    public function testCreatedAt()
    {
        $reference = $this->getObservedSimpleReference();

        $dateTime = new \DateTimeImmutable();
        $this->assertInstanceOf(ObservedSimpleReference::class, $reference->setCreatedAt($dateTime));
        $this->assertSame($dateTime, $reference->getCreatedAt());
    }

    /**
     * @throws \Exception
     */
    public function testUpdatedAt()
    {
        $reference = $this->getObservedSimpleReference();

        $dateTime = new \DateTimeImmutable();
        $this->assertInstanceOf(ObservedSimpleReference::class, $reference->setUpdatedAt($dateTime));
        $this->assertSame($dateTime, $reference->getUpdatedAt());

        $reference->refreshUpdatedAt();

        $this->assertNotSame($dateTime, $reference->getUpdatedAt());
    }

    public function getObservedSimpleReference()
    {
        return new ObservedSimpleReference($this->className, $this->identifier);
    }
}
