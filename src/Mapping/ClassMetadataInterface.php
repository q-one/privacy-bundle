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
 * Interface ClassMetadataInterface.
 */
interface ClassMetadataInterface
{
    /**
     * @return string
     */
    public function getClassName(): string;

    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass(): \ReflectionClass;

    /**
     * @return array
     */
    public function getGroupIds(): array;

    /**
     * @param string $groupId
     *
     * @return bool
     */
    public function hasGroup(string $groupId): bool;

    /**
     * @param string $groupId
     *
     * @return GroupMetadataInterface
     */
    public function getGroup(string $groupId): GroupMetadataInterface;

    /**
     * @return GroupMetadataInterface[]
     */
    public function getGroups(): array;

    /**
     * @param GroupMetadataInterface $group
     *
     * @return mixed
     */
    public function addGroup(GroupMetadataInterface $group);

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasGroupOfField(string $fieldName): bool;

    /**
     * @param string $fieldName
     *
     * @return GroupMetadataInterface
     */
    public function getGroupOfField(string $fieldName): GroupMetadataInterface;

    /**
     * @param Expression $expr
     */
    public function setUserExpr(?Expression $expr): void;

    /**
     * @return Expression|null
     */
    public function getUserExpr(): ? Expression;
}
