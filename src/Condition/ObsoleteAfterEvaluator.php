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

use Doctrine\Common\Persistence\ManagerRegistry;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Verdict\VerdictInterface;

/**
 * Class ObsoleteAfterEvaluator.
 */
class ObsoleteAfterEvaluator implements PredictingEvaluatorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function evaluate($subject, ObservedObjectReferenceInterface $objectReference, ConditionInterface $condition): int
    {
        if (!$condition instanceof ObsoleteAfter) {
            throw new \InvalidArgumentException(sprintf('%s only supports instances of %s', __CLASS__, ObsoleteAfter::class));
        }

        $effectiveHit = new \DateTimeImmutable();
        $intervalHit = $effectiveHit->sub($this->getInterval($condition));

        foreach ($condition->getRefresh() as $trigger) {
            switch ($trigger) {
                case 'create':
                    $effectiveHit = min($effectiveHit, $objectReference->getCreatedAt());
                    break;

                case 'update':
                        if (null !== $objectReference->getUpdatedAt()) {
                            $effectiveHit = min($effectiveHit, $objectReference->getUpdatedAt());
                        }
                    break;
            }
        }

        return $effectiveHit < $intervalHit ? VerdictInterface::JUDGE_OBSOLETE : VerdictInterface::JUDGE_APPROPRIATE;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function predict(
        object $subject,
        ObservedObjectReferenceInterface $objectReference,
        ConditionInterface $condition
    ): ?\DateInterval {
        /* @var ObsoleteAfter $condition */
        return $this->getInterval($condition);
    }

    /**
     * @param ObsoleteAfter $condition
     * @return \DateInterval
     * @throws \Exception
     */
    private function getInterval(ObsoleteAfter $condition)
    {
        return new \DateInterval($condition->getInterval());
    }
}
