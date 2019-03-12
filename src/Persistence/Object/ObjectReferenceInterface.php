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

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * An object reference is determined by its FQDN class name as well as the Doctrine identifier
 * as key => value array. It may be created directly by using the ManagerRegistry.
 */
interface ObjectReferenceInterface
{
    /**
     * The FQDN of the persistence object class.
     *
     * @return string
     */
    public function getClassName(): string;

    /**
     * The Doctrine identifier of the given persistence object.
     *
     * @return array
     */
    public function getIdentifier(): array;

    /**
     * @param ObjectReferenceInterface $objectReference
     *
     * @return bool
     */
    public function equals(ObjectReferenceInterface $objectReference): bool;

    /**
     * Creates a new ObjectReference from an object using the ManagerRegistry as
     * source for the necessary ClassMetadata.
     *
     * @param ManagerRegistry $registry
     * @param object          $object
     *
     * @return ObjectReferenceInterface
     */
    public static function fromObject(ManagerRegistry $registry, object $object): self;

    /**
     * @param ObjectReferenceInterface $objectReference
     *
     * @return ObjectReferenceInterface
     */
    public static function fromReference(ObjectReferenceInterface $objectReference): self;

    /**
     * This should return a human-readable representation of this reference.
     *
     * @return string
     */
    public function __toString(): string;
}
