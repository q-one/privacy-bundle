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

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ObjectExpressionEvaluator.
 */
class ObjectExpressionEvaluator implements ObjectExpressionEvaluatorInterface
{
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
     * @param object $entity
     *
     * @return UserInterface|null
     */
    public function getAttachedUser(object $entity): ? UserInterface
    {
        if (null === $metadata = $this->getMetadata($entity)) {
            return null;
        }

        $variables = [
            'this' => $entity,
        ];

        return $this->expressionLanguage->evaluate(
            $metadata->getUserExpr(),
            $variables
        );
    }

    /**
     * @param object $entity
     *
     * @return string|null
     */
    public function getAttachedSource(object $entity, string $groupId): ? string
    {
        if (null === $metadata = $this->getMetadata($entity)) {
            return null;
        }

        if (!$metadata->hasGroup($groupId)) {
            throw new \InvalidArgumentException(sprintf('Group %s requested but does not exist', $groupId));
        }

        $group = $metadata->getGroup($groupId);

        if (null === $sourceExpr = $group->getSourceExpr()) {
            return null;
        }

        $variables = [
            'this' => $entity,
        ];

        return $this->expressionLanguage->evaluate(
            $sourceExpr,
            $variables
        );
    }

    /**
     * @param object $entity
     * @param string $expression
     * @param array  $variables
     *
     * @return mixed
     */
    public function evaluateFrom(object $entity, string $expression, array $variables = [])
    {
        $variables['this'] = $entity;

        return $this->expressionLanguage->evaluate($expression, $variables);
    }

    private function getMetadata(object $entity): ? ClassMetadataInterface
    {
        $entityClass = ClassUtils::getClass($entity);

        if (!$this->metadataRegistry->hasMetadataFor($entityClass)) {
            return null;
        }

        return $this->metadataRegistry->getMetadataFor($entityClass);
    }
}
