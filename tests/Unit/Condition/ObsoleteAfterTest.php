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

namespace QOne\PrivacyBundle\Tests\Unit\Condition;

use QOne\PrivacyBundle\Condition\ObsoleteAfter;
use QOne\PrivacyBundle\Condition\ObsoleteAfterEvaluator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObsoleteAfterTest extends TestCase
{
    /** @var string */
    protected $interval;

    /** @var string */
    protected $refresh;

    /** @var string */
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->interval = 'PT15D';
        $this->refresh = [];
        $this->message = 'This is a condition message';
    }

    public function testGetInterval()
    {
        $condition = $this->getCondition();

        $this->assertEquals($this->interval, $condition->getInterval());
    }

    public function testGetRefresh()
    {
        $condition = $this->getCondition();

        $this->assertEquals($this->refresh, $condition->getRefresh());
    }

    public function testEvaluatedBy()
    {
        $condition = $this->getCondition();

        $this->assertEquals(ObsoleteAfterEvaluator::class, $condition->evaluatedBy());
    }

    public function testGetMessageTemplate()
    {
        $condition = $this->getCondition();

        $this->assertNotNull($condition->getMessageTemplate());
    }

    public function testGetMessageParameters()
    {
        $condition = $this->getCondition();

        // TODO will provided by the constructor too?
        $this->assertTrue(is_array($condition->getMessageParameters()));
    }

    public function testIntervalException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->interval = null;

        $this->getCondition();
    }

    public function testPossibleTriggersException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->refresh = ['invalid'];

        $this->getCondition();
    }

    private function getCondition()
    {
        return new ObsoleteAfter(
            [
                'interval' => $this->interval,
                'refresh' => $this->refresh,
                'message' => $this->message,
            ]
        );
    }
}
