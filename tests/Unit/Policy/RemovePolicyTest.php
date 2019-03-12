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

namespace QOne\PrivacyBundle\Tests\Unit\Policy;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Policy\RemovePolicy;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class RemovePolicyTest extends TestCase
{
    /** @var VerdictInterface|MockObject */
    protected $verdict;

    /** @var ObjectManager|MockObject */
    protected $om;

    /** @var ManagerRegistry|MockObject */
    private $managerRegistry;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->createMock(ObjectManager::class);
        $this->verdict = $this->createMock(VerdictInterface::class);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
    }

    public function testApply()
    {
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')->willReturn($this->om);

        $policy = $this->getPolicy();
        $this->verdict->expects($this->once())->method('getObject');
        $this->om->expects($this->once())->method('remove');
        $this->om->expects($this->never())->method('flush');

        $policy->apply($this->verdict);
    }

    public function testApplyWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')->willReturn(null);

        $policy = $this->getPolicy();
        $this->verdict->expects($this->never())->method('getObject');
        $this->om->expects($this->never())->method('remove');
        $this->om->expects($this->never())->method('flush');

        $policy->apply($this->verdict);
    }

    private function getPolicy()
    {
        return new RemovePolicy($this->managerRegistry);
    }
}
