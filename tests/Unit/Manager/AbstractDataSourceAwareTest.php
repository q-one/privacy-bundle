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

namespace QOne\PrivacyBundle\Tests\Unit\Manager;

use QOne\PrivacyBundle\Delivery\DeliveryInterface;
use QOne\PrivacyBundle\Manager\AbstractDataSourceAware;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AbstractDataSourceAwareTest extends TestCase
{
    /** @var DeliveryInterface */
    protected $delivery;

    public function setUp()
    {
        parent::setUp();

        $this->delivery = $this->createMock(DeliveryInterface::class);
    }

    public function testAddDataSource()
    {
        $source = $this->getAbstractDataSourceAware();

        $source->addDataSource('http', $this->delivery);

        $this->assertTrue(true);
    }

    public function testAddDataSourceException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $source = $this->getAbstractDataSourceAware();

        $source->addDataSource('http', $this->delivery);

        $source->addDataSource('http', $this->delivery);
    }

    public function testRemoveDataSource()
    {
        $source = $this->getAbstractDataSourceAware();

        $source->addDataSource('http', $this->delivery);
        $source->removeDataSource('http');

        $this->assertTrue(true);
    }

    public function testRemoveDataSourceException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $source = $this->getAbstractDataSourceAware();

        $source->removeDataSource('http');
    }

    private function getAbstractDataSourceAware()
    {
        /** @var AbstractDataSourceAware $source */
        $source = $this->getMockBuilder(AbstractDataSourceAware::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $source;
    }
}
