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

namespace QOne\PrivacyBundle\Annotation;

use QOne\PrivacyBundle\Condition\ConditionInterface;

/**
 * This annotation describes the obsolescence in the context of a class or a single field.
 *
 * If used on a class, this annotation will define default properties for the given group.
 * Otherwise, the obsoletion of the concrete field is described by this annotation; the default
 * properties of the group will be merged with the actual properties defined in the field
 * annotation.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 */
class Obsolesce
{
    /**
     * The group identifier.
     *
     * @var string
     */
    protected $group = 'default';

    /**
     * Array of ConditionInterface annotations that describe the obsoletion strategy.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * The policy service id that shall be used to obsolete the field.
     * If not given, the default policy will be used.
     *
     * @var string|null
     */
    protected $policy;

    /**
     * The audit transformer service id that shall be used when creating a survey.
     * If not given, the default audit transformer will be used.
     *
     * @var string|null
     */
    protected $auditTransformer;

    /**
     * An expression {@see \Symfony\Component\DependencyInjection\ExpressionLanguage}
     * that extracts the source of data from the persistence object.
     *
     * @var string|null
     */
    protected $source;

    /**
     * Obsolesce constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        if (isset($properties['group'])) {
            $this->group = $properties['group'];
        }

        $this->conditions = [];
        $criteria = is_array($properties['criteria']) ? $properties['criteria'] : [];

        foreach ($criteria as $criterion) {
            if (!$criterion instanceof ConditionInterface) {
                throw new \InvalidArgumentException(sprintf('%s annotation allows only criteria that implements %s, but got %s', self::class, ConditionInterface::class, get_class($criterion)));
            }

            $this->conditions[] = $criterion;
        }

        $this->policy = $properties['policy'] ?? null;
        $this->auditTransformer = $properties['auditTransformer'] ?? null;
        $this->source = $properties['source'];
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return null|string
     */
    public function getPolicy(): ?string
    {
        return $this->policy;
    }

    /**
     * @return null|string
     */
    public function getAuditTransformer(): ?string
    {
        return $this->auditTransformer;
    }

    /**
     * @return null|string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return count($this->getConditions()) > 0 || null !== $this->policy || null !== $this->auditTransformer || null !== $this->source;
    }
}
