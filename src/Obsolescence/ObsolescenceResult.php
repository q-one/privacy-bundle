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

use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;

/**
 * Class ObsolescenceResult.
 */
final class ObsolescenceResult extends AbstractResult
{
    /**
     * @var AssetInterface|null
     */
    private $asset;

    /**
     * @var LegacyInterface|null
     */
    private $legacy;

    /**
     * @var \DateInterval|null
     */
    private $prediction;

    /**
     * SimpleObsolescenceResult constructor.
     *
     * @param int                  $status
     * @param AssetInterface|null  $asset
     * @param LegacyInterface|null $legacy
     * @param \DateInterval|null   $prediction
     */
    public function __construct(int $status, ?AssetInterface $asset, ?LegacyInterface $legacy, ?\DateInterval $prediction)
    {
        parent::__construct($status);
        $this->asset = $asset;
        $this->legacy = $legacy;
        $this->prediction = $prediction;
    }

    /**
     * @return AssetInterface
     */
    public function getAsset(): AssetInterface
    {
        return $this->asset;
    }

    /**
     * @return LegacyInterface|null
     */
    public function getLegacy(): ?LegacyInterface
    {
        return $this->legacy;
    }

    /**
     * @return \DateInterval|null
     */
    public function getPrediction(): ?\DateInterval
    {
        return $this->prediction;
    }

    /**
     * @return bool
     */
    public function hasBeenObsoleted(): bool
    {
        return null !== $this->asset && null !== $this->legacy;
    }
}
