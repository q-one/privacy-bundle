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
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;

/**
 * Class Asset.
 *
 * @ORM\Entity(repositoryClass="QOne\PrivacyBundle\ORM\Repository\AssetEntityRepository")
 * @ORM\Table(
 *  name=AssetInterface::COLLECTION_NAME,
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="object_group_idx", columns={"object_class_name", "object_identifier", "group_id"})
 *  },
 *  indexes={
 *     @ORM\Index(name="user_idx", columns={"user_class_name", "user_identifier"}),
 *     @ORM\Index(name="object_idx", columns={"object_class_name", "object_identifier"}),
 *     @ORM\Index(name="evaluation_idx", columns={"evaluation_validated_at", "evaluation_predicted_at"})
 *  }
 * )
 */
class Asset implements AssetInterface
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var EntityReference
     *
     * @ORM\Embedded(class="QOne\PrivacyBundle\ORM\Entity\EntityReference")
     */
    private $user;

    /**
     * @var ObservedEntityReference
     *
     * @ORM\Embedded(class="QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference")
     */
    private $object;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $groupId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable = true)
     */
    private $source;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $evaluationPredictedAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $evaluationValidatedAt;

    /**
     * Asset constructor.
     *
     * @param EntityReference         $user
     * @param ObservedEntityReference $object
     * @param string                  $groupId
     * @param string|null             $source
     */
    public function __construct(EntityReference $user, ObservedEntityReference $object, string $groupId, ?string $source)
    {
        $this->user = $user;
        $this->object = $object;
        $this->groupId = $groupId;
        $this->source = $source;
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
    public function setUser(ObjectReferenceInterface $user): AssetInterface
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject(): ObservedObjectReferenceInterface
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function setSource(?string $source): AssetInterface
    {
        $this->source = $source;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvaluationPredictedAt(): ?\DateTimeInterface
    {
        return $this->evaluationPredictedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvaluationPredictedAt(\DateInterval $interval): void
    {
        try {
            $this->evaluationPredictedAt = (new \DateTimeImmutable())->add($interval);
        } catch (\Exception $e) {
            throw new \RuntimeException('Couldn\'t update evaluation prediction date', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEvaluationValidatedAt(): ?\DateTimeInterface
    {
        return $this->evaluationValidatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvaluationValidatedAt(): void
    {
        try {
            $this->evaluationPredictedAt = new \DateTimeImmutable();
        } catch (\Exception $e) {
            throw new \RuntimeException('Couldn\'t update evaluation validation date', 0, $e);
        }
    }
}
