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

namespace QOne\PrivacyBundle\Tests\Unit\Survey;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Survey\Group;
use QOne\PrivacyBundle\Survey\RecordInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class GroupTest extends TestCase
{
    /** @var string */
    protected $id = 'id';

    /** @var array */
    protected $conditions = [];

    /** @var array */
    protected $records = [];

    /** @var string */
    protected $source = 'foo';

    /** @var ConditionInterface|MockObject */
    protected $condition;

    protected function setUp()
    {
        parent::setUp();
    }

    public function testConstruct()
    {

        $this->condition = $this->createMock(ConditionInterface::class);
        $this->condition->expects($this->exactly(2))->method('getMessageTemplate')->willReturn('{param}');
        $this->condition->expects($this->exactly(2))->method('getMessageParameters')->willReturn(['param' => 'foo']);
        $this->conditions[] = [$this->condition];
        $this->conditions[] = $this->condition;

        $record = $this->createMock(RecordInterface::class);
        $this->records[] = $record;

        $group = $this->getGroup();

        $this->assertEquals($this->id, $group->getId());
        $this->assertEquals($this->source, $group->getSource());
    }

    public function testConstructWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->condition = $this->createMock(ConditionInterface::class);
        $this->conditions[] = [$this->condition];
        $this->conditions[] = $this->condition;

        $record = $this->createMock(\stdClass::class);
        $this->records[] = $record;

        $group = $this->getGroup();

        $this->assertEquals($this->id, $group->getId());
        $this->assertEquals($this->source, $group->getSource());
    }

    public function testGetter()
    {
        $group = $this->getGroup();

        $this->assertEquals($this->id, $group->getId());
        $this->assertEquals($this->conditions, $group->getConditions());
        $this->assertEquals($this->records, $group->getRecords());
        $this->assertEquals($this->source, $group->getSource());
    }

    private function getGroup()
    {
        return new Group($this->id, $this->conditions, $this->records, $this->source);
    }
}
