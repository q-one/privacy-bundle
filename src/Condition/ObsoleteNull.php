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

namespace QOne\PrivacyBundle\Condition;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class ObsoleteNull implements ConditionInterface
{
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
        $this->message = $properties['message'] ?? 'Never obsoleted!';
    }

    /**
     * {@inheritdoc}
     */
    public function evaluatedBy(): string
    {
        return ObsoleteNullEvaluator::class;
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
        ];
    }
}
