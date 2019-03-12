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
 * The log position is a simple persistence object that saves the current log position
 * of the legacy log.
 */
interface StateInterface
{
    const COLLECTION_NAME = '_qone_privacy_state';

    const KEY_LEGACY_POSITION = 'legacy_position';

    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @return int|null
     */
    public function getIntValue();

    /**
     * @param int|null $intValue
     */
    public function setIntValue(?int $intValue);

    /**
     * @return string|null
     */
    public function getStringValue(): ?string;

    /**
     * @param string|null $stringValue
     */
    public function setStringValue(?string $stringValue): void;
}
