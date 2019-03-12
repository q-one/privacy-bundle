<?php

/*
 * Copyright 2018 Q.One Technologies GmbH, Essen
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

/**
 * Class AbstractObsoleteExpr.
 */
abstract class AbstractObsoleteExpr implements ConditionInterface, JudgementMapInterface
{
    /** @var string */
    protected $expr;

    /** @var string */
    protected $message;

    /**
     * AbstractObsoleteExpr constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        if (!isset($properties['expr']) || !isset($properties['message'])) {
            throw new \InvalidArgumentException('Invalid properties. Expecting expr and message.');
        }

        $this->expr = $properties['expr'];
        $this->message = $properties['message'];
    }

    /**
     * @return string
     */
    public function getExpr(): string
    {
        return $this->expr;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluatedBy(): string
    {
        return ObsoleteExprEvaluator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageTemplate(): string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParameters(): array
    {
        return [
            'expr' => $this->expr,
        ];
    }
}
