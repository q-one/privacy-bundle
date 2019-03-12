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
            $property = $refClass->getProperty($fieldName);
            $property->setAccessible(true);

            $newValue = $this->transformFieldValue($object, $fieldName, $property->getValue($object));
            $property->setValue($object, $newValue);
        }

        $om->persist($object);
    }
}
