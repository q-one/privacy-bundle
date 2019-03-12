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

namespace QOne\PrivacyBundle\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;

/**
 * @ORM\Entity(repositoryClass="QOne\PrivacyBundle\ORM\Repository\StateEntityRepository")
 * @ORM\Table(name=StateInterface::COLLECTION_NAME)
 */
class State implements StateInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="pkey", length=20, options={"fixed": true})
     *
     * @var string
     */
    private $key;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $intValue;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $stringValue;

    /**
     * State constructor.
     *
     * @param string      $key
     * @param int|null    $intValue
     * @param string|null $stringValue
     */
    public function __construct(string $key, ?int $intValue = null, ?string $stringValue = null)
    {
        $this->key = $key;
        $this->setIntValue($intValue);
        $this->setStringValue($stringValue);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return int|null
     */
    public function getIntValue(): ?int
    {
        return $this->intValue;
    }

    /**
     * @param int|null $intValue
     */
    public function setIntValue(?int $intValue): void
    {
        $this->intValue = $intValue;
    }

    /**
     * @return string|null
     */
    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    /**
     * @param string|null $stringValue
     */
    public function setStringValue(?string $stringValue): void
    {
        $this->stringValue = $stringValue;
    }
}
