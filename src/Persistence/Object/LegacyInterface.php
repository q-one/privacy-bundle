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

namespace QOne\PrivacyBundle\Persistence\Object;

use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;

/**
 * While an Asset represents a current data set, a Legacy is a data set that has already
 * been obsoleted. It's a logfile that may be replayed in the case of a database failure
 * with a following restore.
 */
interface LegacyInterface
{
    const COLLECTION_NAME = '_qone_privacy_legacy';

    /**
     * Unique, numerical identifier of this legacy.
     *
     * @return int|null
     *
     * @see StateInterface
     */
    public function getId(): ?int;

    /**
     * Returns a reference to the associated user.
     *
     * @return ObjectReferenceInterface
     */
    public function getUser(): ObjectReferenceInterface;

    /**
     * Returns a reference to the associated object.
     *
     * @return ObjectReferenceInterface
     */
    public function getObject(): ObjectReferenceInterface;

    /**
     * Returns the fields which the given policy has been applied to.
     *
     * @return array
     */
    public function getFields(): array;

    /**
     * Returns the service id of the policy that has been applied to the data.
     *
     * @return string
     */
    public function getApplicationPolicy(): string;

    /**
     * Returns the timestamp at which the policy has been applied to the data.
     *
     * @return \DateTimeInterface
     */
    public function getApplicationTimestamp(): \DateTimeInterface;

    /**
     * @return GroupMetadataInterface
     */
    public function restoreGroupMetadata(): GroupMetadataInterface;
}
