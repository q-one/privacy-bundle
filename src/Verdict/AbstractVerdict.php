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
 * Class AbstractVerdict.
 */
abstract class AbstractVerdict implements VerdictInterface
{
    /** @var object */
    protected $object;

    /** @var ClassMetadataInterface */
    protected $classMetadata;

    /** @var GroupMetadataInterface */
    protected $groupMetadata;

    /** @var array */
    protected $votes;

    /**
     * AbstractVerdict constructor.
     *
     * @param object                 $object
     * @param ClassMetadataInterface $classMetadata
     * @param GroupMetadataInterface $groupMetadata
     * @param array                  $votes
     */
    public function __construct(object $object, ClassMetadataInterface $classMetadata, GroupMetadataInterface $groupMetadata, array $votes = [])
    {
        $this->resetVotes();
        $this->object = $object;
        $this->classMetadata = $classMetadata;
        $this->groupMetadata = $groupMetadata;

        foreach ($votes as $vote => $carr) {
            foreach ($carr as $condition) {
                if (!$condition instanceof ConditionInterface) {
                    throw new \UnexpectedValueException(
                        sprintf('%s is not a ConditionInterface', get_class($condition))
                    );
                }
                $this->addVote($condition, $vote);
            }
        }
    }

    /**
     * @param object                 $object
     * @param ClassMetadataInterface $classMetadata
     * @param GroupMetadataInterface $groupMetadata
     *
     * @return VerdictInterface
     */
    public static function create(object $object, ClassMetadataInterface $classMetadata, GroupMetadataInterface $groupMetadata): VerdictInterface
    {
        return new static($object, $classMetadata, $groupMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata(): ClassMetadataInterface
    {
        return $this->classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupMetadata(): GroupMetadataInterface
    {
        return $this->groupMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function addVote(ConditionInterface $condition, int $vote): VerdictInterface
    {
        if (!isset($this->votes[$vote])) {
            throw new \InvalidArgumentException(sprintf('No such possible vote: %d', $vote));
        }

        $this->votes[$vote][] = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObsoleteVotes(): array
    {
        return $this->votes[VerdictInterface::JUDGE_OBSOLETE];
    }

    /**
     * {@inheritdoc}
     */
    public function getAppropriateVotes(): array
    {
        return $this->votes[VerdictInterface::JUDGE_APPROPRIATE];
    }

    /**
     * {@inheritdoc}
     */
    public function getKeepVotes(): array
    {
        return $this->votes[VerdictInterface::JUDGE_KEEP];
    }

    /**
     * {@inheritdoc}
     */
    public function resetVotes(): void
    {
        $this->votes = [
            VerdictInterface::JUDGE_OBSOLETE => [],
            VerdictInterface::JUDGE_APPROPRIATE => [],
            VerdictInterface::JUDGE_KEEP => [],
        ];
    }
}
