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

namespace QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use QOne\PrivacyBundle\Annotation as Privacy;
use QOne\PrivacyBundle\Condition as Condition;

/**
 * Class Address.
 *
 * @ORM\Entity()
 *
 * @Privacy\Audited(user="this.getUser()")
 * @Privacy\Obsolesce(
 *     criteria={
 *       @Condition\ObsoleteIf(expr="this.isObsolete()", message="Test message.")
 *     },
 *     source="this.getSource()",
 *     policy="QOne\PrivacyBundle\Policy\NullPolicy",
 *     auditTransformer="QOne\PrivacyBundle\Transformer\IdentityFieldTransformer"
 * )
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\User", inversedBy="addresses")
     * @ORM\JoinColumn(nullable = true, name="user_id")
     *
     * @var User|null
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Privacy\Obsolesce
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Privacy\Obsolesce
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Privacy\Obsolesce
     */
    protected $zip;

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street): void
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isObsolete(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'From Whatever';
    }
}
