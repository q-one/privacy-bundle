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

namespace QOne\PrivacyBundle\Obsolescence;

use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;

/**
 * Class LegacyResult.
 */
class LegacyResult extends AbstractResult
{
    /**
     * @var LegacyInterface|null
     */
    private $legacy;

    /**
     * @var int
     */
    private $currentPosition;

    /**
     * SimpleObsolescenceResult constructor.
     *
     * @param int                  $status
     * @param LegacyInterface|null $legacy
     * @param int                  $currentPosition
     */
    public function __construct(int $status, ?LegacyInterface $legacy, int $currentPosition)
    {
        parent::__construct($status);

        $this->legacy = $legacy;
        $this->currentPosition = $currentPosition;
    }

    /**
     * @return LegacyInterface|null
     */
    public function getLegacy(): ?LegacyInterface
    {
        return $this->legacy;
    }

    /**
     * @return int
     */
    public function getCurrentPosition(): int
    {
        return $this->currentPosition;
    }
}
