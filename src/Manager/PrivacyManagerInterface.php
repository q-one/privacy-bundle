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

namespace QOne\PrivacyBundle\Manager;

use Doctrine\Common\Collections\Collection;
use QOne\PrivacyBundle\Obsolescence\LegacyResult;
use QOne\PrivacyBundle\Obsolescence\ObsolescenceResult;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;

/**
 * Interface PrivacyManagerInterface.
 */
interface PrivacyManagerInterface
{
    /**
     * @param \DateInterval|string|null $reevaluationInterval
     */
    public function setReevaluationInterval($reevaluationInterval): void;

    /**
     * This method is responsible for fetching all assets related to a given user.
     * The requested assets are returned as a Doctrine {@see Collection}.
     *
     * @param ObjectReferenceInterface $user
     *
     * @return Collection
     */
    public function getAssetsForUser(ObjectReferenceInterface $user): Collection;

    /**
     * @param string $objectManagerName
     *
     * @return ObsolescenceResult
     */
    public function doObsolescence(string $objectManagerName): ObsolescenceResult;

    /**
     * @param string $objectManagerName
     *
     * @return LegacyResult
     */
    public function doLegacy(string $objectManagerName): LegacyResult;
}
