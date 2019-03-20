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

use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface ObjectExpressionEvaluatorInterface.
 */
interface ObjectExpressionEvaluatorInterface
{
    public function parseExpression(string $expr): ParsedExpression;

    public function getAttachedUser(ClassMetadataInterface $classMetadata, object $entity): ? UserInterface;

    public function getAttachedSource(GroupMetadataInterface $groupMetadata, object $entity): ? string;
}
