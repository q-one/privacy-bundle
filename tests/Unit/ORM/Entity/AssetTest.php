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

namespace QOne\PrivacyBundle\Tests\Unit\ORM\Entity;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\ORM\Entity\Asset;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AssetTest extends TestCase
{
    /** @var EntityReference|MockObject */
    protected $user;

    /** @var ObservedEntityReference|MockObject */
    protected $object;

    /** @var string */
    protected $groupId;

    /** @var Asset */
    protected $asset;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(EntityReference::class);
        $this->object = $this->createMock(ObservedEntityReference::class);
        $this->groupId = 'test';
    }

    public function testConstruct()
    {
        $asset = $this->getAsset();
        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testGetter()
    {
        $asset = $this->getAsset();

        $this->assertEquals($asset->getUser(), $this->user);
        $this->assertEquals($asset->getObject(), $this->object);
        $this->assertEquals($asset->getGroupId(), $this->groupId);
        $this->assertNull($asset->getEvaluationValidatedAt());
        $this->assertNull($asset->getEvaluationPredictedAt());
        $this->assertEquals($asset->getSource(), 'source');
        $this->assertNull($asset->getId());
    }

    private function getAsset()
    {
        return new Asset($this->user, $this->object, $this->groupId, 'source');
    }
}
