<?php

namespace QOne\PrivacyBundle\Tests\Unit\Survey\Format\Serializer;

use QOne\PrivacyBundle\Survey\Format\Serializer\IdentityClassDiscriminatorMapping;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class IdentityClassDiscriminatorMappingTest extends TestCase
{
    /**
     * @var string
     */
    private $typeProperty;

    protected function setUp()
    {
        parent::setUp();

        $this->typeProperty = 'type';
    }

    public function testGetClassForType()
    {
        $identityClass = $this->getIdentityClass();
        $this->assertSame($this->typeProperty, $identityClass->getClassForType($this->typeProperty));

    }

    public function testGetMappedObjectType()
    {
        $identityClass = $this->getIdentityClass();

        $this->assertSame(\stdClass::class, $identityClass->getMappedObjectType(new \stdClass()));
    }

    public function getIdentityClass(): IdentityClassDiscriminatorMapping
    {
        return new IdentityClassDiscriminatorMapping($this->typeProperty);
    }

}
