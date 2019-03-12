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

/**
 * An asset represents a single data set that features obsoletion and is evaluated in
 * certain time frames. It is attributed to an user and an object, has a specific group,
 * and is able to be obsoleted.
 *
 * The earliest next evaluation time may also be predicted, if the group supports this
 * behaviour.
 */
interface AssetInterface
{
    const COLLECTION_NAME = '_qone_privacy_assets';

    /***
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int;

    /**
     * Returns a reference to the associated user.
     *
     * @return ObjectReferenceInterface
     */
    public function getUser(): ObjectReferenceInterface;

    /**
     * Sets a reference to the associated user.
     *
     * @param ObjectReferenceInterface $user
     *
     * @return AssetInterface
     */
    public function setUser(ObjectReferenceInterface $user): self;

    /**
     * Returns a reference to the associated object.
     *
     * @return ObservedObjectReferenceInterface
     */
    public function getObject(): ObservedObjectReferenceInterface;

    /**
     * Returns the group identifier that is associated with this asset.
     *
     * @return string
     */
    public function getGroupId(): string;

    /**
     * Returns the source this asset has been gathered from.
     *
     * @return string
     */
    public function getSource(): ? string;

    /**
     * Set the source this asset has been gathered from.
     *
     * @param string $source
     *
     * @return AssetInterface
     */
    public function setSource(string $source): self;

    /**
     * Returns the timestamp after which the next evaluation shall happen, if present.
     *
     * @return \DateTimeInterface|null
     */
    public function getEvaluationPredictedAt(): ? \DateTimeInterface;

    /**
     * @param \DateInterval $interval
     */
    public function updateEvaluationPredictedAt(\DateInterval $interval): void;

    /**
     * Returns the timestamp of the last evaluation, if present.
     *
     * @return \DateTimeInterface|null
     */
    public function getEvaluationValidatedAt(): ? \DateTimeInterface;

    public function updateEvaluationValidatedAt(): void;
}
