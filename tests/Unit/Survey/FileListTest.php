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
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Survey\File;
use QOne\PrivacyBundle\Survey\FileList;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class FileListTest extends TestCase
{
    /** @var File|MockObject */
    protected $file;

    /** @var File|MockObject */
    protected $file2;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->file = $this->createMock(File::class);
        $this->file2 = $this->createMock(File::class);
    }

    public function testConstructor()
    {
        $fileList = $this->getFileList();

        $this->assertInstanceOf(FileList::class, $fileList);
    }

    public function testAdd()
    {
        $fileList = $this->getFileList();

        /** @var File|MockObject $currentFile */
        $currentFile = $this->createMock(File::class);

        /** @var File|MockObject $file */
        $file = $this->createMock(File::class);

        $observedObjectReference = $this->createMock(ObservedObjectReferenceInterface::class);
        $observedObjectReference->expects($this->once())->method('equals')->willReturn(true);
        $currentFile->expects($this->once())->method('getObject')->willReturn($observedObjectReference);

        $fileList->setFiles([$currentFile]);
        $fileList->add($file);

        $this->assertEquals(1, $fileList->count());
    }

    public function testAddAll()
    {
        $fileList = $this->getFileList();

        $otherList = $this->getFileList();
        $otherList->add($this->file);
        $otherList->add($this->file);

        $fileList->addAll($otherList);

        $this->assertEquals(2, $fileList->count());
    }

    public function testOffsetExists()
    {
        $fileList = $this->getFileList();

        $this->assertFalse($fileList->offsetExists(0));

        $fileList->add($this->file);

        $this->assertTrue($fileList->offsetExists(0));
    }

    public function testOffsetGet()
    {
        $fileList = $this->getFileList();

        $fileList->add($this->file);

        $this->assertEquals($this->file, $fileList->offsetGet(0));
    }

    public function testGetException()
    {
        $this->expectException(\OutOfBoundsException::class);

        $fileList = $this->getFileList();

        $fileList->get(0);
    }

    public function testSet()
    {
        $fileList = $this->getFileList();

        $fileList->set(0, $this->file);
        $fileList->set(10, $this->file);

        $this->assertEquals($this->file, $fileList->get(0));
        $this->assertEquals($this->file, $fileList->get(10));
    }

    public function testRemove()
    {
        $fileList = $this->getFileList();

        $fileList->add($this->file);
        $fileList->add($this->file);

        $this->assertEquals($this->file, $fileList->get(0));
        $this->assertEquals($this->file, $fileList->get(1));

        $fileList->remove(0);

        $this->assertFalse($fileList->has(0));
    }

    public function testOffsetUnset()
    {
        $fileList = $this->getFileList();

        $fileList->set(11, $this->file);

        $this->assertEquals($this->file, $fileList->get(11));

        $fileList->offsetUnset(11);

        $this->assertFalse($fileList->has(11));
    }

    public function testOffsetSet()
    {
        $fileList = $this->getFileList();

        $fileList->offsetSet(null, $this->file);

        $this->assertTrue($fileList->has(0));

        $fileList->offsetSet(11, $this->file);

        $this->assertTrue($fileList->has(11));
    }

    private function getFileList()
    {
        return new FileList();
    }
}
