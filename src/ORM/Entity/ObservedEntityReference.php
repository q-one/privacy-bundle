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
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;

/**
 * Class ObservedEntityReference.
 *
 * @ORM\Embeddable
 */
class ObservedEntityReference extends EntityReference implements ObservedObjectReferenceInterface
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updatedAt;

    public function __construct(string $className, array $identifier)
    {
        parent::__construct($className, $identifier);

        try {
            $this->setCreatedAt(new \DateTimeImmutable());
        } catch (\Exception $e) {
            throw new \RuntimeException('Couldn\'t determine creation date', 0, $e);
        }
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $dt
     *
     * @return ObservedObjectReferenceInterface
     */
    public function setCreatedAt(\DateTimeInterface $dt): ObservedObjectReferenceInterface
    {
        $this->createdAt = $dt;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): ? \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $dt
     *
     * @return ObservedObjectReferenceInterface
     */
    public function setUpdatedAt(?\DateTimeInterface $dt = null): ObservedObjectReferenceInterface
    {
        $this->updatedAt = $dt;

        return $this;
    }

    /**
     * @return ObservedObjectReferenceInterface
     */
    public function refreshUpdatedAt(): ObservedObjectReferenceInterface
    {
        try {
            $this->setUpdatedAt(new \DateTimeImmutable());
        } catch (\Exception $e) {
            throw new \RuntimeException('Couldn\'t determine current date', 0, $e);
        }

        return $this;
    }
}
