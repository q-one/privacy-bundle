<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
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

namespace QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use QOne\PrivacyBundle\ORM\Entity\Assets\Asset;
use QOne\PrivacyBundle\ORM\Entity\Assets\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\Assets\ObservedEntityReference;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class AssetFixtures.
 */
class AssetFixtures extends Fixture
{
    public const ASSET_REFERENCE = 'asset';

    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user');

        /** @var EntityReference $userReference */
        $userReference = EntityReference::fromObject($this->registry, $user);

        $address = $this->getReference('address');
        /** @var ObservedEntityReference $addressReference */
        $addressReference = ObservedEntityReference::fromObject($this->registry, $address);

        $asset = new Asset($userReference, $addressReference, 'default', 'source');

        $manager->persist($asset);
        $manager->flush();

        $this->addReference(self::ASSET_REFERENCE, $asset);
    }
}
