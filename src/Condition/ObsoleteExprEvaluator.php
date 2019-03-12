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
use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class ObsoleteExprEvaluator.
 */
class ObsoleteExprEvaluator implements ContainerAwareInterface, EvaluatorInterface
{
    use ContainerAwareTrait;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * ObsoleteExprEvaluator constructor.
     *
     * @param ManagerRegistry    $managerRegistry
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ManagerRegistry $managerRegistry, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
    }

    /**
     * @param $subject
     * @param ObservedObjectReferenceInterface $objectReference
     * @param ConditionInterface|AbstractObsoleteExpr $condition
     *
     * @return int
     */
    public function evaluate($subject, $objectReference, ConditionInterface $condition): int
    {
        if (!$condition instanceof JudgementMapInterface) {
            throw new \InvalidArgumentException(sprintf('%s only supports instances of %s', __CLASS__, JudgementMapInterface::class));
        }

        $variables = [
            'this' => $subject,
            'container' => $this->container,
            'om' => $this->managerRegistry->getManagerForClass(ClassUtils::getClass($subject)),
            'registry' => $this->managerRegistry,
        ];

        /** @var bool $result */
        $result = $this->expressionLanguage->evaluate($condition->getExpr(), $variables);
        $judgementMap = $condition->getJudgementMap();

        if (isset($judgementMap[$result])) {
            return $judgementMap[$result];
        }

        return $condition->getDefaultJudgement();
    }
}
