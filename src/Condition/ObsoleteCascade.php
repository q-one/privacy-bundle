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
 * @Annotation
 * @Target("ANNOTATION")
 */
class ObsoleteCascade implements ConditionInterface
{
    /**
     * @var string
     */
    protected $entity;

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
        if (!isset($properties['entity'])) {
            throw new \InvalidArgumentException('Required property "entity" not set');
        }

        $this->entity = $properties['entity'];
        $this->message = $properties['message'] ?? 'Obsoleted because deletion of entity "{entity}"';
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluatedBy(): string
    {
        return ObsoleteCascadeEvaluator::class;
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
        return ['entity' => $this->entity];
    }
}
