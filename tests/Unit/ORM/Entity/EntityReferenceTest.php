<?php

/*
 * Copyright 2018 Q.One Technologies GmbH, Essen
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

namespace QOne\PrivacyBundle\Tests\Unit\ORM\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityReferenceTest extends TestCase
{
    /** @var string */
    protected $className;

    /** @var array */
    protected $identifier;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->className = TestEntity::class;
        $this->identifier = ['id'];
    }

    public function testConstruct()
    {
        $entityReference = $this->getEntityReference();

        $this->assertInstanceOf(EntityReference::class, $entityReference);
    }

    public function testGetter()
    {
        $entityReference = $this->getEntityReference();

        $this->assertEquals($entityReference->getClassName(), $this->className);
        $this->assertEquals($entityReference->getIdentifier(), $this->identifier);
    }

    private function getEntityReference()
    {
        return new EntityReference($this->className, $this->identifier);
    }
}

class TestEntity
{
}
