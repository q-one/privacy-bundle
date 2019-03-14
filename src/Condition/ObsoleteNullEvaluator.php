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

use Doctrine\Common\Persistence\ManagerRegistry;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Verdict\VerdictInterface;

/**
 * Class ObsoleteAfterEvaluator.
 */
class ObsoleteNullEvaluator implements PredictingEvaluatorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function evaluate($subject, ObservedObjectReferenceInterface $objectReference, ConditionInterface $condition): int
    {
        if (!$condition instanceof ObsoleteNull) {
            throw new \InvalidArgumentException(sprintf('%s only supports instances of %s', __CLASS__, ObsoleteNull::class));
        }

        return VerdictInterface::JUDGE_APPROPRIATE;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function predict(
        object $subject,
        ObservedObjectReferenceInterface $objectReference,
        ConditionInterface $condition
    ): ?\DateInterval {
        /* @var ObsoleteNull $condition */
        return null;
    }

    /**
     * @param ObsoleteNull $condition
     *
     * @return \DateInterval
     *
     * @throws \Exception
     */
    private function getInterval(ObsoleteNull $condition)
    {
        return null;
    }
}
