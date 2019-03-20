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
 * Interface GroupMetadataInterface.
 */
interface GroupMetadataInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return array
     */
    public function getFields(): array;

    /**
     * @param string $field
     */
    public function addField(string $field): void;

    /**
     * @return array
     */
    public function getConditions(): array;

    /**
     * @return string|null
     */
    public function getPolicy(): ? string;

    /**
     * @return string|null
     */
    public function getAuditTransformer(): ? string;

    /**
     * @return Expression|null
     */
    public function getSourceExpr(): ? Expression;
}
