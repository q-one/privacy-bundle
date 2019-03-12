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

use QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObservedEntityReferenceTest extends TestCase
{
    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $identifier;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->className = TestEntity::class;
        $this->identifier = ['id'];
    }

    public function testConstruct()
    {
        $observedEntityReference = $this->getObservedEntityReference();

        $this->assertInstanceOf(ObservedEntityReference::class, $observedEntityReference);
    }

    public function testGetter()
    {
        $observedEntityReference = $this->getObservedEntityReference();

        $this->assertEquals($this->identifier, $observedEntityReference->getIdentifier());
        $this->assertEquals($this->className, $observedEntityReference->getClassName());
    }

    public function testSetter()
    {
        $observedEntityReference = $this->getObservedEntityReference();

        $this->assertEquals($observedEntityReference, $observedEntityReference->setCreatedAt($this->createdAt));
        $this->assertEquals($observedEntityReference, $observedEntityReference->setUpdatedAt($this->updatedAt));

        $this->assertEquals($this->createdAt, $observedEntityReference->getCreatedAt());
        $this->assertEquals($this->updatedAt, $observedEntityReference->getUpdatedAt());
    }

    /**
     * @throws \Exception
     */
    public function testRefreshUpdatedAt()
    {
        $observedEntityReference = $this->getObservedEntityReference();

        $this->assertEquals($observedEntityReference, $observedEntityReference->refreshUpdatedAt());
        $this->assertNotEquals($this->updatedAt, $observedEntityReference->getUpdatedAt());
    }

    private function getObservedEntityReference()
    {
        return new ObservedEntityReference($this->className, $this->identifier);
    }
}
