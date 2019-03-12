<?php

/*
 * Copyright 2018 Q.One Technologies GmbH, Essen
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
 * Additionally to being an ObjectReference, an ObservedObjectReference also saves the timestamp
 * of the first persist as well as the last update operation.
 */
interface ObservedObjectReferenceInterface extends ObjectReferenceInterface
{
    /**
     * Returns the timestamp of the creation of the observed persistence object.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface;

    /**
     * Sets the timestamp of the creation of the observed persistence object.
     *
     * @param \DateTimeInterface $dt
     *
     * @return ObservedObjectReferenceInterface
     */
    public function setCreatedAt(\DateTimeInterface $dt): self;

    /**
     * Returns the timestamp of the last update of the observed persistence object, if present.
     *
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ? \DateTimeInterface;

    /**
     * Sets the timestamp of the last update of the observed persistence object.
     * Will only be set on the first update operation after the initial persist (^= creation).
     *
     * @param \DateTimeInterface|null $dt
     *
     * @return ObservedObjectReferenceInterface
     */
    public function setUpdatedAt(?\DateTimeInterface $dt = null): self;

    /**
     * Sets the "updatedAt" timestamp to the current date and time.
     *
     * @return ObservedObjectReferenceInterface
     */
    public function refreshUpdatedAt(): self;
}
