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

namespace QOne\PrivacyBundle\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;

/**
 * Class Legacy.
 *
 * @ORM\Entity(repositoryClass="QOne\PrivacyBundle\ORM\Repository\LegacyEntityRepository")
 * @ORM\Table(
 *  name=LegacyInterface::COLLECTION_NAME,
 *  indexes={
 *     @ORM\Index(name="user_idx", columns={"user_class_name", "user_identifier"}),
 *     @ORM\Index(name="object_idx", columns={"object_class_name", "object_identifier"}),
 *     @ORM\Index(name="application_timestamp_idx", columns={"application_timestamp"})
 *  }
 * )
 */
class Legacy implements LegacyInterface
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var EntityReference
     *
     * @ORM\Embedded(class="QOne\PrivacyBundle\ORM\Entity\EntityReference")
     */
    private $user;

    /**
     * @var EntityReference
     *
     * @ORM\Embedded(class="QOne\PrivacyBundle\ORM\Entity\EntityReference")
     */
    private $object;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $fields;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $applicationPolicy;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $applicationTimestamp;

    /**
     * Legacy constructor.
     *
     * @param ObjectReferenceInterface $user
     * @param ObjectReferenceInterface $object
     * @param array                    $fields
     * @param string                   $applicationPolicy
     * @param \DateTimeInterface       $applicationTimestamp
     *
     * @throws \Exception
     */
    public function __construct(ObjectReferenceInterface $user, ObjectReferenceInterface $object, array $fields, string $applicationPolicy, \DateTimeInterface $applicationTimestamp = null)
    {
        $this->user = $user;
        $this->object = $object;
        $this->fields = $fields;
        $this->applicationPolicy = $applicationPolicy;
        $this->applicationTimestamp = $applicationTimestamp ?: new \DateTimeImmutable();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): ObjectReferenceInterface
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject(): ObjectReferenceInterface
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationPolicy(): string
    {
        return $this->applicationPolicy;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationTimestamp(): \DateTimeInterface
    {
        return $this->applicationTimestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreGroupMetadata(): GroupMetadataInterface
    {
        return new class($this->getId(), $this->getFields(), $this->getApplicationPolicy()) implements GroupMetadataInterface {
            /** @var string */
            private $id;

            /** @var array */
            private $fields;

            /** @var string */
            private $policy;

            /**
             *  constructor.
             *
             * @param string $id
             * @param array  $fields
             * @param string $policy
             */
            public function __construct(string $id, array $fields, string $policy)
            {
                $this->id = $id;
                $this->fields = $fields;
                $this->policy = $policy;
            }

            /**
             * @return string
             */
            public function getId(): string
            {
                return sprintf('@legacy#%s', $this->id);
            }

            /**
             * @return array
             */
            public function getFields(): array
            {
                return $this->fields;
            }

            /**
             * @param string $field
             */
            public function addField(string $field): void
            {
                throw new \BadMethodCallException();
            }

            /**
             * @return array
             */
            public function getConditions(): array
            {
                return [];
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
                throw new \BadMethodCallException();
            }

            /**
             * @return string|null
             */
            public function getSourceExpr(): ?string
            {
                throw new \BadMethodCallException();
            }
        };
    }
}
