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

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class ObsoleteAfter implements ConditionInterface
{
    const POSSIBLE_TRIGGERS = ['create', 'update'];

    /**
     * @var string
     */
    protected $interval;

    /**
     * @var array|null
     */
    protected $refresh;

    /**
     * @var string
     */
    protected $message;

    /**
     * ObsoleteAfter constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        if (!isset($properties['interval'])) {
            throw new \InvalidArgumentException('Required property "interval" not set');
        }

        $this->interval = $properties['interval'];
        $this->refresh = $properties['refresh'] ?? ['create'];
        $this->message = $properties['message'] ?? 'Obsoleted because time period {interval} exceeded';

        if($invalidTriggers = array_diff($this->refresh, self::POSSIBLE_TRIGGERS)) {
            throw new \InvalidArgumentException(sprintf('Invalid refresh triggers "%s"', implode('", "', $invalidTriggers)));
        }
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @return array
     */
    public function getRefresh(): ? array
    {
        return $this->refresh;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluatedBy(): string
    {
        return ObsoleteAfterEvaluator::class;
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
            'interval' => $this->interval,
            'refresh' => $this->refresh,
        ];
    }
}
