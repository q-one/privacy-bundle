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

namespace QOne\PrivacyBundle\Policy;

/**
 * This policy truncates a given field by removing everything but the last three characters, and
 * filling the void with asterisks. If the original value has less than or equal to three characters,
 * the remaining characters shall accordingly be less.
 */
class TruncatePolicy extends UpdateFieldValuePolicy
{
    /**
     * @var int
     */
    protected $truncateBut = 3;

    /**
     * @param int $truncateBut
     */
    public function setTruncateBut(int $truncateBut): void
    {
        if ($truncateBut < 0) {
            throw new \InvalidArgumentException('Parameter $truncateBut is expected to be greater or equal to zero');
        }

        $this->truncateBut = $truncateBut;
    }

    /**
     * {@inheritdoc}
     */
    public function transformFieldValue(object $subject, string $fieldName, $currentValue)
    {
        $stringLength = strlen($currentValue);
        $truncateLength = $stringLength - $this->truncateBut;

        if ($stringLength > 1 && $stringLength <= $this->truncateBut) {
            $truncateLength = $stringLength - ($stringLength - 1);
        }

        if ($truncateLength > 0) {
            $replaceWith = str_repeat('*', $truncateLength);
            $currentValue = substr_replace($currentValue, $replaceWith, 0, $truncateLength);
        }

        return $currentValue;
    }
}
