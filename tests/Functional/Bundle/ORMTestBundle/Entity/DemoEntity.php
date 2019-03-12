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

namespace QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use QOne\PrivacyBundle\Annotation\Audited;
use QOne\PrivacyBundle\Annotation\Obsolesce;
use QOne\PrivacyBundle\Condition\ObsoleteAfter;
use QOne\PrivacyBundle\Condition\ObsoleteIf;

/**
 * @ORM\Entity
 * @Audited(user="this.getUser()")
 * @Obsolesce(group="alt", criteria={@ObsoleteIf(expr="true === false", message="True is not false")})
 */
class DemoEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Obsolesce(criteria={@ObsoleteAfter(interval="PT60S")})
     *
     * @var string
     */
    protected $str;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $foo = '';

    /**
     * @ORM\Column(type="string", nullable= true)
     * @Obsolesce(group="alt", criteria={@ObsoleteAfter(interval="PT60M")})
     *
     * @var string
     */
    protected $bar = '';

    /**
     * @ORM\ManyToOne(targetEntity=DemoUser::class)
     */
    protected $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStr(): string
    {
        return $this->str;
    }

    /**
     * @param string $str
     */
    public function setStr(string $str): void
    {
        $this->str = $str;
    }

    /**
     * @return DemoUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param DemoUser $user
     */
    public function setUser(DemoUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @param string $foo
     */
    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getBar(): string
    {
        return $this->bar;
    }

    /**
     * @param string $bar
     */
    public function setBar(string $bar): void
    {
        $this->bar = $bar;
    }
}
