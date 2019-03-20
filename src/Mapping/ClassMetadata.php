<?php

/*
 * Copyright (c) 2018-2019 Q.One Technologies GmbH, Essen
 * All rights reserved.
 *
 * This file is part of CloudBasket.
 *
 * NOTICE: The contents of this file are CONFIDENTIAL and MUST NOT be published
 * nor redistributed without prior written permission.
 */

namespace QOne\PrivacyBundle\Mapping;

use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Class ClassMetadata.
 */
class ClassMetadata implements ClassMetadataInterface
{
    /** @var string */
    protected $className;

    /** @var array|GroupMetadataInterface[] */
    protected $groups = [];

    /** @var Expression|null */
    protected $userExpr;

    /**
     * ClassMetadata constructor.
     *
     * @param string          $className
     * @param array           $groups
     * @param Expression|null $userExpr
     */
    public function __construct(string $className, array $groups = [], ?Expression $userExpr = null)
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
     * {@inheritdoc}
     */
    public function setUserExpr(?Expression $expr): void
    {
        $this->userExpr = $expr;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserExpr(): ?Expression
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
}
