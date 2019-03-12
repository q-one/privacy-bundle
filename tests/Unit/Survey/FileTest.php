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
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Survey\File;
use QOne\PrivacyBundle\Survey\GroupInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class FileTest extends TestCase
{
    /** @var ObservedObjectReferenceInterface|MockObject */
    protected $object;

    /** @var array */
    protected $ownGroups;

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->object = $this->createMock(ObservedObjectReferenceInterface::class);
        $this->object->expects($this->once())->method('getCreatedAt')->willReturn(new \DateTime());
        $this->object->expects($this->once())->method('getUpdatedAt')->willReturn(new \DateTime());
        $group1 = $this->createMock(GroupInterface::class);
        $group2 = $this->createMock(GroupInterface::class);
        $this->ownGroups = [$group1, $group2];
    }

    /**
     * @throws \Exception
     */
    public function testGetter()
    {
        $file = $this->getFile();

        $file->addGroups($this->ownGroups);


        $this->assertInstanceOf(File::class, $file);
        $this->assertInstanceOf(ObservedObjectReferenceInterface::class, $file->getObject());
        $this->assertEquals('source', $file->getSource());
        $this->assertTrue(is_array($file->getGroups()));
        $this->assertInstanceOf(\DateTimeInterface::class, $file->getCollectedAt());
    }

    /**
     * @return File
     * @throws \Exception
     */
    public function testConstructWitException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->ownGroups[] = new \stdClass();
        return new File($this->object, 'source', $this->ownGroups, new \DateTime());
    }

    /**
     * @return File
     * @throws \Exception
     */
    private function getFile()
    {
        return new File($this->object, 'source', $this->ownGroups, new \DateTime());
    }
}
