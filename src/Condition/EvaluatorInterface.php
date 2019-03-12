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

namespace QOne\PrivacyBundle\Condition;

use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;

/**
 * Evaluates a given condition regarding a specific subject object.
 */
interface EvaluatorInterface
{
    /**
     * Returns one of {@link VerdictInterface::JUDGE_KEEP},
     * {@link VerdictInterface::JUDGE_JUDGE_APPROPRIATE}
     * or {@link VerdictInterface::JUDGE_OBSOLETE} after evaluating
     * the Condition against the given subject.
     *
     * @param object                           $subject
     * @param ObservedObjectReferenceInterface $objectReference
     * @param ConditionInterface               $condition
     *
     * @return int
     */
    public function evaluate(object $subject, ObservedObjectReferenceInterface $objectReference, ConditionInterface $condition): int;
}
