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
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\Legacy;
use QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference;
use QOne\PrivacyBundle\Policy\NullPolicy;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\Address;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\User;

class LegacyFixtures extends Fixture
{
    public const LEGACY_REFERENCE = 'legacy';

    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user');
        /** @var Address $object */
        $object = $this->getReference('address');

        $userReference = EntityReference::fromObject($this->registry, $user);
        $objectReference = ObservedEntityReference::fromObject($this->registry, $object);
        $fields = ['street', 'zip', 'city'];
        $policy = NullPolicy::class;

        $legacy = new Legacy($userReference, $objectReference, $fields, $policy);

        $manager->persist($legacy);
        $manager->flush();

        $this->addReference(self::LEGACY_REFERENCE, $legacy);
    }
}
