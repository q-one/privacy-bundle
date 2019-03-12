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
use Doctrine\Common\Util\ClassUtils;
use InvalidArgumentException;
use QOne\PrivacyBundle\Exception\EvaluationException;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class ObsoleteCascadeEvaluator.
 */
class ObsoleteCascadeEvaluator implements EvaluatorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var ExpressionLanguage|null
     */
    private $expressionLanguage;

    /**
     * ObsoleteCascadeEvaluator constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param ExpressionLanguage|null $expressionLanguage
     */
    public function __construct(ManagerRegistry $managerRegistry, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
    }

    /**
     * @param $subject
     * @param $objectReference
     * @param ConditionInterface $condition
     *
     * @return int
     * @throws EvaluationException
     * @throws InvalidArgumentException
     */
    public function evaluate($subject, $objectReference, ConditionInterface $condition): int
    {
        if (!$condition instanceof ObsoleteCascade) {
            throw new InvalidArgumentException(
                sprintf('%s only supports instances of %s', __CLASS__, ObsoleteCascade::class)
            );
        }

        $variables = [
            'this' => $subject,
        ];

        $result = $this->expressionLanguage->evaluate($condition->getEntity(), $variables);

        if (null !== $result && !is_object($result)) {
            throw new EvaluationException(
                sprintf('Expected \$result to be an object or NULL, but got %s', gettype($result))
            );
        }

        if (null === $result) {
            return VerdictInterface::JUDGE_OBSOLETE;
        } else {
            $resultClass = ClassUtils::getClass($subject);
            $rm = $this->managerRegistry->getManagerForClass($resultClass);
            $ri = $rm->getClassMetadata($resultClass)->getIdentifierValues($result);

            if (null === $rm->find($resultClass, $ri)) {
                return VerdictInterface::JUDGE_OBSOLETE;
            }
        }

        return VerdictInterface::JUDGE_APPROPRIATE;
    }
}
