<?php

/*
 * Copyright (c) 2018-2019 Q.One Technologies GmbH, Essen
 * All rights reserved.
 *
 * This file is part of CloudBasket.
 *
 * NOTICE: The contents of this file are CONFIDENTIAL and MUST NOT be published
 * nor redistributed without prior written permission.
 */

namespace QOne\PrivacyBundle\Mapping;

use Symfony\Component\ExpressionLanguage\Expression;

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

    /** @var Expression|null */
    protected $sourceExpr;

    /**
     * GroupMetadata constructor.
     *
     * @param string          $id
     * @param array           $fields
     * @param array           $conditions
     * @param string          $policy
     * @param string          $auditTransformer
     * @param Expression|null $sourceExpr
     */
    public function __construct(string $id, array $fields, array $conditions, ?string $policy, ?string $auditTransformer, ?Expression $sourceExpr)
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
     * @return string|null
     */
    public function getPolicy(): ?string
    {
        return $this->policy;
    }

    /**
     * @return string|null
     */
    public function getAuditTransformer(): ?string
    {
        return $this->auditTransformer;
    }

    /**
     * @return Expression|null
     */
    public function getSourceExpr(): ?Expression
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
