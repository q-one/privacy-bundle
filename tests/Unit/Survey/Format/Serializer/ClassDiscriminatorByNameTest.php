<?php

namespace QOne\PrivacyBundle\Tests\Unit\Survey\Format\Serializer;

use QOne\PrivacyBundle\Survey\Format\Serializer\ClassDiscriminatorByName;
use QOne\PrivacyBundle\Survey\Format\Serializer\IdentityClassDiscriminatorMapping;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ClassDiscriminatorByNameTest extends TestCase
{
    /**
     * @var array
     */
    private $whitelist;

    /**
     * @var boolean
     */
    private $subclassesAllowed;

    protected function setUp()
    {
        parent::setUp();

        $this->whitelist = [];
        $this->subclassesAllowed = false;
    }

    public function testGetMappingForMappedObject()
    {
        $this->whitelist[] = \stdClass::class;
        $discriminator = $this->getDiscriminator();
        $result = $discriminator->getMappingForMappedObject(new \stdClass());

        $this->assertInstanceOf(IdentityClassDiscriminatorMapping::class, $result);
    }

    public function testGetMappingForMappedObjectOnNull()
    {
        $discriminator = $this->getDiscriminator();
        $result = $discriminator->getMappingForMappedObject(new \stdClass());

        $this->assertNull($result);
    }

    public function testGetMappingForClass()
    {
        $this->whitelist[] = \stdClass::class;
        $discriminator = $this->getDiscriminator();
        $result = $discriminator->getMappingForClass(\stdClass::class);

        $this->assertInstanceOf(IdentityClassDiscriminatorMapping::class, $result);
    }

    public function testGetMappingForClassOnNUll()
    {
        $this->subclassesAllowed = true;
        $this->whitelist[] = TestCase::class;
        $discriminator = $this->getDiscriminator();
        $result = $discriminator->getMappingForClass(\stdClass::class);

        $this->assertNull($result);
    }

    public function testGetMappingForClassOnIsA()
    {
        $this->subclassesAllowed = true;
        $this->whitelist[] = \stdClass::class;
        $discriminator = $this->getDiscriminator();
        $result = $discriminator->getMappingForClass(TestIsAClazz::class);

        $this->assertInstanceOf(IdentityClassDiscriminatorMapping::class, $result);
    }

    public function getDiscriminator()
    {
        return new ClassDiscriminatorByName($this->whitelist, $this->subclassesAllowed);
    }
}

class TestIsAClazz extends \stdClass
{

}
