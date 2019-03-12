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

namespace QOne\PrivacyBundle\Verdict;

use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;

/**
 * Collects votes from Evaluator instances to distinguish what to do with the
 * given persistence object.
 */
interface VerdictInterface
{
    const JUDGE_OBSOLETE = 1;
    const JUDGE_APPROPRIATE = 0;
    const JUDGE_KEEP = -1;

    /**
     * @param object                 $object
     * @param ClassMetadataInterface $classMetadata
     * @param GroupMetadataInterface $groupMetadata
     *
     * @return VerdictInterface
     */
    public static function create(object $object, ClassMetadataInterface $classMetadata, GroupMetadataInterface $groupMetadata): self;

    /**
     * @return object
     */
    public function getObject(): object;

    /**
     * @return ClassMetadataInterface
     */
    public function getClassMetadata(): ClassMetadataInterface;

    /**
     * @return GroupMetadataInterface
     */
    public function getGroupMetadata(): GroupMetadataInterface;

    /**
     * @param ConditionInterface $condition
     * @param int                $vote
     *
     * @return VerdictInterface
     */
    public function addVote(ConditionInterface $condition, int $vote): self;

    /**
     * @return array
     */
    public function getObsoleteVotes(): array;

    /**
     * @return array
     */
    public function getAppropriateVotes(): array;

    /**
     * @return array
     */
    public function getKeepVotes(): array;

    /**
     * Resets all votes in this verdict.
     */
    public function resetVotes(): void;

    /**
     * This method returns, subject to the votes in this verdict,
     * if the given object must be obsoleted.
     *
     * @return bool
     */
    public function isObsolete(): bool;
}
