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
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Mapping\ClassMetadata;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistry;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Policy\UpdateFieldValuePolicy;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class UpdateFieldValuePolicyTest extends TestCase
{
    /** @var ObjectManager|MockObject */
    protected $om;

    /** @var MetadataRegistry|MockObject */
    protected $metadataRegistry;

    /** @var ManagerRegistry|MockObject */
    private $managerRegistry;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->createMock(ObjectManager::class);
        $this->om->expects($this->once())->method('persist');
        $this->om->expects($this->never())->method('flush');

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')->willReturn($this->om);
        $this->metadataRegistry = $this->createMock(MetadataRegistryInterface::class);
    }

    /**
     * @throws \ReflectionException
     */
    public function testApply()
    {
        $policy = $this->getPolicy();

        /** @var VerdictInterface|MockObject $verdict */
        $verdict = $this->createMock(VerdictInterface::class);

        $metaData = $this->createMock(ClassMetadata::class);
        $groupMetadata = $this->createMock(GroupMetadataInterface::class);

        $classMetadata = $this->getMockBuilder(ClassMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getClassName',
                    'getReflectionClass',
                    'getGroupIds',
                    'hasGroup',
                    'getGroup',
                    'getGroups',
                    'addGroup',
                    'hasGroupOfField',
                    'getGroupOfField',
                    'setUserExpr',
                    'getUserExpr'
                ]
            )->getMock();


        $refClass = new ReflectionClass('QOne\PrivacyBundle\Tests\Unit\Policy\TestObject');
        $classMetadata->expects($this->once())->method('getReflectionClass')->willReturn($refClass);

        $object = new TestObject(1);

        $this->metadataRegistry->expects($this->never())->method('getMetadataFor')->willReturn($metaData);

        $verdict->expects($this->once())->method('getGroupMetadata')->willReturn($groupMetadata);
        $verdict->expects($this->any())->method('getClassMetadata')->willReturn($classMetadata);

        $verdict->expects($this->any())->method('getObject')->willReturn($object);

        $groupMetadata->expects($this->once())->method('getFields')->willReturn(['id']);

        $policy->apply($verdict);
    }

    private function getPolicy()
    {
        /** @var UpdateFieldValuePolicy $policy */
        $policy = $this->getMockBuilder(UpdateFieldValuePolicy::class)
            ->setConstructorArgs([$this->managerRegistry, $this->metadataRegistry])
            ->getMockForAbstractClass();

        return $policy;
    }
}

class TestObject
{
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
