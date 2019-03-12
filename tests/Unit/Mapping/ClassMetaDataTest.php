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

namespace QOne\PrivacyBundle\Tests\Unit\Mapping;

use QOne\PrivacyBundle\Mapping\ClassMetadata;
use QOne\PrivacyBundle\Mapping\GroupMetadata;
use QOne\PrivacyBundle\Policy\NullPolicy;
use QOne\PrivacyBundle\Transformer\IdentityFieldTransformer;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ClassMetaDataTest extends TestCase
{
    public function testConstructor()
    {
        $classMetaData = $this->getClassMetaData();

        $this->assertInstanceOf(ClassMetadata::class, $classMetaData);
    }

    public function testGetClassName()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertEquals(TestMetaClass::class, $classMetaData->getClassName());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetReflectionClass()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertInstanceOf(\ReflectionClass::class, $classMetaData->getReflectionClass());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetReflectionClassException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $classMetaData = $this->getClassMetaData('');
        $classMetaData->getReflectionClass();
    }

    public function testGetGroupIds()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertTrue(is_array($classMetaData->getGroupIds()));
    }

    public function testGetGroup()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertInstanceOf(GroupMetadata::class, $classMetaData->getGroup('group1'));
    }

    public function testGetGroupException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $classMetaData = $this->getClassMetaData();
        $classMetaData->getGroup('failed');
    }

    public function testHasGroup()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertTrue($classMetaData->hasGroup('group1'));
        $this->assertFalse($classMetaData->hasGroup('group2'));
    }

    public function testHasGroupOfField()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertTrue($classMetaData->hasGroupOfField('id'));
        $this->assertTrue($classMetaData->hasGroupOfField('name'));
        $this->assertFalse($classMetaData->hasGroupOfField('failed'));
    }

    public function testGetGroupOfField()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertInstanceOf(GroupMetadata::class, $classMetaData->getGroupOfField('id'));
    }

    public function testGetGroupOfFieldException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $classMetaData = $this->getClassMetaData();
        $this->assertInstanceOf(GroupMetadata::class, $classMetaData->getGroupOfField('failed'));
    }

    public function testGetUserExpr()
    {
        $classMetaData = $this->getClassMetaData();
        $this->assertEquals('obj.getId()', $classMetaData->getUserExpr());
    }

    public function testAddGroup()
    {
        $classMetaData = $this->getClassMetaData();

        $group = $this->createMock(GroupMetadata::class);
        $group->expects($this->once())->method('getId')->wilLReturn('test');

        $classMetaData->addGroup($group);

        $this->assertTrue($classMetaData->hasGroup('test'));
    }

    public function testSetUserExpr()
    {
        $classMetaData = $this->getClassMetaData();

        $classMetaData->setUserExpr('this.isTest');

        $this->assertEquals($classMetaData->getUserExpr(), 'this.isTest');
    }

    private function getClassMetaData(string $className = TestMetaClass::class)
    {
        $groupMetaData = new GroupMetadata(
            'group1',
            ['id', 'name'],
            [],
            NullPolicy::class,
            IdentityFieldTransformer::class,
            'obj.getObj()'
        );

        return new ClassMetadata($className, [$groupMetaData], 'obj.getId()');
    }
}

class TestMetaClass
{
}
