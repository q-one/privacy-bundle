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

namespace QOne\PrivacyBundle\Mapping;

/**
 * Class GroupMetadata.
 */
class GroupMetadata implements GroupMetadataInterface
{
    /** @var string */
    protected $id;

    /** @var array */
    protected $fields;

    /** @var array */
    protected $conditions;

    /** @var string|null */
    protected $policy;

    /** @var string|null */
    protected $auditTransformer;

    /** @var string|null */
    protected $sourceExpr;

    /**
     * GroupMetadata constructor.
     *
     * @param string $id
     * @param array  $fields
     * @param array  $conditions
     * @param string $policy
     * @param string $auditTransformer
     * @param string $sourceExpr
     */
    public function __construct(string $id, array $fields, array $conditions, ?string $policy, ?string $auditTransformer, ?string $sourceExpr)
    {
        $this->id = $id;
        $this->fields = $fields;
        $this->conditions = $conditions;
        $this->policy = $policy;
        $this->auditTransformer = $auditTransformer;
        $this->sourceExpr = $sourceExpr;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
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
    public function getSourceExpr(): ?string
    {
        return $this->sourceExpr;
    }

    /**
     * @param string $field
     */
    public function addField(string $field): void
    {
        $this->fields[] = $field;
    }
}
