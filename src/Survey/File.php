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

namespace QOne\PrivacyBundle\Survey;

use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\ObservedSimpleReference;

class File implements FileInterface
{
    /**
     * @var ObservedObjectReferenceInterface
     */
    private $object;

    /**
     * @var null|string
     */
    private $source;

    /**
     * @var GroupInterface[]
     */
    private $groups;

    /**
     * @var \DateTimeInterface
     */
    private $collectedAt;

    /**
     * File constructor.
     * @param ObservedObjectReferenceInterface $object
     * @param string|null $source
     * @param array $groups
     * @param \DateTimeInterface|null $collectedAt
     * @throws \Exception
     */
    public function __construct(ObservedObjectReferenceInterface $object, ?string $source, array $groups, \DateTimeInterface $collectedAt = null)
    {
        $this->object = ObservedSimpleReference::fromReference($object);
        $this->source = $source;
        $this->groups = [];
        $this->collectedAt = $collectedAt ?: new \DateTimeImmutable();

        foreach ($groups as $group) {
            if (!$group instanceof GroupInterface) {
                throw new \InvalidArgumentException(sprintf('Expected $groups to be an array of GroupInterface, but got %s', get_class($group)));
            }

            $this->groups[] = $group;
        }
    }

    /**
     * @return ObservedObjectReferenceInterface
     */
    public function getObject(): ObservedObjectReferenceInterface
    {
        return $this->object;
    }

    /**
     * @return null|string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param GroupInterface $group
     */
    public function addGroup(GroupInterface $group): void
    {
        $this->groups[] = $group;
    }

    /**
     * @param array $groups
     */
    public function addGroups(array $groups): void
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCollectedAt(): \DateTimeInterface
    {
        return $this->collectedAt;
    }
}
