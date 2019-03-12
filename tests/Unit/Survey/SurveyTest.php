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

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Survey\FileList;
use QOne\PrivacyBundle\Survey\FileListInterface;
use QOne\PrivacyBundle\Survey\Survey;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class SurveyTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGetter()
    {
        $survey = $this->getSurvey();

        $this->assertInstanceOf(ObjectReferenceInterface::class, $survey->getUser());
        $this->assertInstanceOf(FileListInterface::class, $survey->getFiles());
        $this->assertInstanceOf(\DateTime::class, $survey->getCreatedAt());
    }

    /**
     * @return Survey
     * @throws \Exception
     */
    private function getSurvey()
    {
        /** @var ObjectReferenceInterface|MockObject $entityReference */
        $entityReference = $this->createMock(ObjectReferenceInterface::class);
        /** @var FileListInterface|MockObject $fileList */
        $fileList = $this->createMock(FileListInterface::class);

        return new Survey($entityReference, $fileList, new \DateTime());
    }
}
