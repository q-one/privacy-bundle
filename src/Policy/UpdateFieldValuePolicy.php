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

namespace QOne\PrivacyBundle\Policy;

use Doctrine\Common\Persistence\ManagerRegistry;
use QOne\PrivacyBundle\Mapping\MetadataRegistry;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Transformer\FieldTransformerInterface;
use QOne\PrivacyBundle\Verdict\VerdictInterface;

/**
 * Base-class for simple, field altering policies in which the apply() method handles
 * the whole persistence layer.
 *
 * The value returned by the transformFieldValue() method will be set as the new value
 * in the persistence object, which is going to be updated using the appropriate ObjectManager
 * instance retrieved from the Doctrine ManagerRegistry.
 */
abstract class UpdateFieldValuePolicy extends ManagerRegistryAwarePolicy implements FieldTransformerInterface
{
    /** @var MetadataRegistry */
    protected $metaDataRegistry;

    public function __construct(ManagerRegistry $managerRegistry, MetadataRegistryInterface $metaDataRegistry)
    {
        parent::__construct($managerRegistry);
        $this->metaDataRegistry = $metaDataRegistry;
    }

    /**
     * @param VerdictInterface $verdict
     *
     * @throws \ReflectionException
     */
    public function apply(VerdictInterface $verdict): void
    {
        $object = $verdict->getObject();
        $om = $this->getManagerFor($verdict);

        $groupMetaData = $verdict->getGroupMetadata();
        $fields = $groupMetaData->getFields();
        $refClass = $verdict->getClassMetadata()->getReflectionClass();

        foreach ($fields as $fieldName) {
            $methodName = 'set'.ucfirst($fieldName);

            if ($refClass->hasMethod($methodName)) {
                $property = $refClass->getProperty($fieldName);
                $property->setAccessible(true);

                $refMethod = $refClass->getMethod($methodName);
                $refMethod->setAccessible(true);
                $newValue = $this->transformFieldValue($object, $fieldName, $property->getValue($object));
                $refMethod->invoke($object, $newValue);
            } else {
                $property = $refClass->getProperty($fieldName);
                $property->setAccessible(true);
                $newValue = $this->transformFieldValue($object, $fieldName, $property->getValue($object));
                $property->setValue($object, $newValue);
            }
        }

        $om->persist($object);
    }
}
