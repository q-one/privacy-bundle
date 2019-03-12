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

namespace QOne\PrivacyBundle\Persistence\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;

interface AssetRepositoryInterface extends ObjectRepository
{
    /**
     * @param ObjectReferenceInterface $user
     *
     * @return array
     */
    public function findAssetsForUser(ObjectReferenceInterface $user): array;

    /**
     * @param ObjectReferenceInterface $user
     * @param ObjectReferenceInterface $object
     * @param string                   $groupId
     *
     * @return AssetInterface|null
     */
    public function findAsset(
        ObjectReferenceInterface $user,
        ObjectReferenceInterface $object,
        string $groupId
    ): ? AssetInterface;

    /**
     * @param ObjectReferenceInterface $object
     * @param string                   $groupId
     *
     * @return AssetInterface|null
     */
    public function findAssetForObjectGroup(
        ObjectReferenceInterface $object,
        string $groupId
    ): ?AssetInterface;

    /**
     * @param \DateInterval|null $validationInterval
     *
     * @return AssetInterface|null
     */
    public function findNextValidationAsset(?\DateInterval $validationInterval): ?AssetInterface;
}
