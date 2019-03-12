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

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\Legacy;
use QOne\PrivacyBundle\Policy\NullPolicy;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class LegacyTest extends TestCase
{
    /**
     * @var EntityReference|MockObject
     */
    protected $entityReference;

    /**
     * @var EntityReference|MockObject
     */
    protected $objectReference;

    /**
     * @var array
     */
    protected $fields;

    /** @var string */
    protected $applicationPolicy;

    /**
     * @var \DateTimeInterface
     */
    protected $applicationTimestamp;

    /**
     * @var Legacy
     */
    protected $legacy;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->entityReference = $this->createMock(EntityReference::class);
        $this->objectReference = $this->createMock(EntityReference::class);
        $this->fields = ['street', 'city'];
        $this->applicationPolicy = NullPolicy::class;
        $this->applicationTimestamp = new \DateTime();
    }

    public function testConstruct()
    {
        $legacy = $this->getLegacy();

        $this->assertInstanceOf(Legacy::class, $legacy);
    }

    public function testGetter()
    {
        $legacy = $this->getLegacy();

        $this->assertNull($legacy->getId());
        $this->assertEquals($this->entityReference, $legacy->getUser());
        $this->assertEquals($this->objectReference, $legacy->getObject());
        $this->assertEquals($this->fields, $legacy->getFields());
        $this->assertEquals($this->applicationPolicy, $legacy->getApplicationPolicy());
        $this->assertEquals($this->applicationTimestamp, $legacy->getApplicationTimestamp());
    }

    private function getLegacy()
    {
        return new Legacy(
            $this->entityReference,
            $this->objectReference,
            $this->fields,
            $this->applicationPolicy,
            $this->applicationTimestamp
        );
    }
}
