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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ObjectExpressionEvaluator.
 */
class ObjectExpressionEvaluator implements ContainerAwareInterface, ObjectExpressionEvaluatorInterface
{
    use ContainerAwareTrait;

    /**
     * @var MetadataRegistryInterface
     */
    protected $metadataRegistry;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * ObjectExpressionEvaluator constructor.
     *
     * @param MetadataRegistryInterface $metadataRegistry
     * @param ExpressionLanguage        $expressionLanguage
     */
    public function __construct(MetadataRegistryInterface $metadataRegistry, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->metadataRegistry = $metadataRegistry;
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function parseExpression(string $expr): ParsedExpression
    {
        return $this->expressionLanguage->parse($expr, array_keys($this->getReferences(null)));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachedUser(ClassMetadataInterface $classMetadata, object $entity): ? UserInterface
    {
        return $this->expressionLanguage->evaluate(
            $classMetadata->getUserExpr(),
            $this->getReferences($entity)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachedSource(GroupMetadataInterface $groupMetadata, object $entity): ? string
    {
        if (null === $sourceExpr = $groupMetadata->getSourceExpr()) {
            return null;
        }

        return $this->expressionLanguage->evaluate(
            $sourceExpr,
            $this->getReferences($entity)
        );
    }

    /**
     * Returns an associative array with the usable variables in expressions.
     *
     * @param object|null $that
     *
     * @return array
     */
    private function getReferences(?object $that)
    {
        return [
            'this' => $that,
            'container' => $this->container,
        ];
    }
}
