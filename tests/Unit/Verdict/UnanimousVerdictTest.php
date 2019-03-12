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

namespace QOne\PrivacyBundle\Tests\Unit\Verdict;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Verdict\UnanimousVerdict;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;

class UnanimousVerdictTest extends TestCase
{
    public function testIsObsolete()
    {
        $verdict = $this->getVerdict();

        $this->assertFalse($verdict->isObsolete());
    }

    private function getVerdict()
    {
        $object = (object)[];
        /** @var GroupMetadataInterface|MockObject $groupMetadata */
        $groupMetadata = $this->createMock(GroupMetadataInterface::class);
        /** @var ClassMetadataInterface|MockObject $classMetadata */
        $classMetadata = $this->createMock(ClassMetadataInterface::class);

        return new UnanimousVerdict($object, $classMetadata, $groupMetadata);
    }
}
