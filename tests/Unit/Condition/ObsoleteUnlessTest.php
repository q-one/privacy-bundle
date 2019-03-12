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

use QOne\PrivacyBundle\Condition\ObsoleteExprEvaluator;
use QOne\PrivacyBundle\Condition\ObsoleteUnless;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObsoleteUnlessTest extends TestCase
{
    /** @var string */
    protected $expr;

    /** @var string */
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->expr = 'obj.hasUnlessExpr()';
        $this->message = 'Because the expression is false.';
    }

    public function testGetExpr()
    {
        $condition = $this->getCondition();

        $this->assertEquals($this->expr, $condition->getExpr());
    }

    public function testEvaluatedBy()
    {
        $condition = $this->getCondition();

        $this->assertEquals(ObsoleteExprEvaluator::class, $condition->evaluatedBy());
    }

    public function testGetMessageTemplate()
    {
        $condition = $this->getCondition();

        $this->assertEquals($this->message, $condition->getMessageTemplate());
    }

    public function testGetMessageParameters()
    {
        $condition = $this->getCondition();

        $this->assertArrayHasKey('expr', $condition->getMessageParameters());
        $this->assertEquals($this->expr, $condition->getMessageParameters()['expr']);
    }

    public function testException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expr = null;

        $this->getCondition();
    }

    public function testGetJudgementMap()
    {
        $condition = $this->getCondition();

        $this->assertEquals([true => VerdictInterface::JUDGE_KEEP], $condition->getJudgementMap());
    }

    public function testGetDefaultJudgement()
    {
        $condition = $this->getCondition();

        $this->assertEquals(VerdictInterface::JUDGE_APPROPRIATE, $condition->getDefaultJudgement());
    }

    private function getCondition()
    {
        return new ObsoleteUnless(['expr' => $this->expr, 'message' => $this->message]);
    }
}
