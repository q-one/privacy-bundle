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

namespace QOne\PrivacyBundle\Mapping;

use QOne\PrivacyBundle\Survey\Group;

/**
 * Class ClassMetadata.
 */
class ClassMetadata implements ClassMetadataInterface
{
    /** @var string */
    protected $className;

    /** @var array|GroupMetadataInterface[] */
    protected $groups = [];

    /** @var string */
    protected $userExpr;

    /**
     * ClassMetadata constructor.
     *
     * @param string $className
     * @param array  $groups
     * @param string $userExpr
     */
    public function __construct(string $className, array $groups = [], string $userExpr = '')
    {
        $this->className = $className;
        $this->userExpr = $userExpr;

        foreach ($groups as $group) {
            if ($group instanceof GroupMetadataInterface) {
                $this->groups[] = $group;
            } else {
                throw new \InvalidArgumentException(sprintf('%s is not a GroupDataMetaInterface', get_class($group)));
            }
        }
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return \ReflectionClass
     *
     * @throws \ReflectionException
     */
    public function getReflectionClass(): \ReflectionClass
    {
        if (!$this->className) {
            throw new \InvalidArgumentException('Class name is missing');
        }

        return new \ReflectionClass($this->className);
    }

    /**
     * @return array
     */
    public function getGroupIds(): array
    {
        $groupIds = [];

        foreach ($this->groups as $group) {
            $groupIds[] = $group->getId();
        }

        return $groupIds;
    }

    /**
     * @param string $groupId
     *
     * @return bool
     */
    public function hasGroup(string $groupId): bool
    {
        /** @var GroupMetadata $group */
        foreach ($this->groups as $group) {
            if ($groupId === $group->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $groupId
     *
     * @return GroupMetadataInterface
     */
    public function getGroup(string $groupId): GroupMetadataInterface
    {
        foreach ($this->groups as $group) {
            if ($groupId === $group->getId()) {
                return $group;
            }
        }

        throw new \InvalidArgumentException(sprintf('Group with ID %s is missing', $groupId));
    }

    /**
     * @return GroupMetadataInterface[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasGroupOfField(string $fieldName): bool
    {
        foreach ($this->groups as $group) {
            if (in_array($fieldName, $group->getFields())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $fieldName
     *
     * @return GroupMetadataInterface
     */
    public function getGroupOfField(string $fieldName): GroupMetadataInterface
    {
        foreach ($this->groups as $group) {
            if (in_array($fieldName, $group->getFields())) {
                return $group;
            }
        }

        throw new \InvalidArgumentException(sprintf('FieldName with ID %s is missing', $fieldName));
    }

    /**
     * @return null|string
     */
    public function getUserExpr(): ?string
    {
        return $this->userExpr;
    }

    /**
     * @param GroupMetadataInterface $group
     */
    public function addGroup(GroupMetadataInterface $group)
    {
        $this->groups[] = $group;
    }

    /**
     * @param string $expr
     */
    public function setUserExpr(string $expr): void
    {
        $this->userExpr = $expr;
    }
}
