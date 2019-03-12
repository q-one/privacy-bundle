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

use QOne\PrivacyBundle\Mapping\GroupMetadata;
use QOne\PrivacyBundle\Policy\NullPolicy;
use QOne\PrivacyBundle\Transformer\IdentityFieldTransformer;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class GroupMetaDataTest extends TestCase
{
    public function testConstructor()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertInstanceOf(GroupMetadata::class, $groupMetaData);
    }

    public function testGetId()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertEquals('group1', $groupMetaData->getId());
    }

    public function testGetFields()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertTrue(is_array($groupMetaData->getFields()));
    }

    public function testGetConditions()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertTrue(is_array($groupMetaData->getConditions()));
    }

    public function testGetPolicy()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertEquals(NullPolicy::class, $groupMetaData->getPolicy());
    }

    public function testGetAuditTransformer()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertEquals(IdentityFieldTransformer::class, $groupMetaData->getAuditTransformer());
    }

    public function testGetSourceExpr()
    {
        $groupMetaData = $this->getGroupMetaData();
        $this->assertEquals('obj.getObj()', $groupMetaData->getSourceExpr());
    }

    public function testAddField()
    {
        $groupMetaData = $this->getGroupMetaData();

        $groupMetaData->addField('test');

        $this->assertTrue(in_array('test', $groupMetaData->getFields()));
    }

    private function getGroupMetaData()
    {
        return new GroupMetadata(
            'group1',
            ['id', 'name'],
            [],
            NullPolicy::class,
            IdentityFieldTransformer::class,
            'obj.getObj()'
        );
    }
}
