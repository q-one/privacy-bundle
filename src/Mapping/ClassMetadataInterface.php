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
     * @param string $expr
     */
    public function setUserExpr(string $expr): void;

    /**
     * @return string|null
     */
    public function getUserExpr(): ? string;
}
