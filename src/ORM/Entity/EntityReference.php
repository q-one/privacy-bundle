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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping as ORM;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use Doctrine\Common\Util\ClassUtils;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceTrait;

/**
 * Class EntityReference.
 *
 * @ORM\Embeddable
 */
class EntityReference implements ObjectReferenceInterface
{
    use ObjectReferenceTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="binary", length = 20, options={"fixed": true})
     */
    protected $className;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $classNameRaw;

    /**
     * @var string
     *
     * @ORM\Column(type="binary", length = 20, options={"fixed": true})
     */
    protected $identifier;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $identifierRaw = [];

    /**
     * EntityReference constructor.
     *
     * @param string $className
     * @param array  $identifier
     */
    public function __construct(string $className, array $identifier)
    {
        $this->classNameRaw = $className;
        $this->identifierRaw = $identifier;

        $this->className = static::createSearchKey($className);
        $this->identifier = static::createSearchKey($identifier);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->classNameRaw;
    }

    /**
     * @return array
     */
    public function getIdentifier(): array
    {
        return $this->identifierRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(ObjectReferenceInterface $objectReference): bool
    {
        return $this->getClassName() === $objectReference->getClassName() && $this->getIdentifier() === $objectReference->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public static function fromObject(ManagerRegistry $registry, object $object): ObjectReferenceInterface
    {
        $clazz = ClassUtils::getClass($object);

        try {
            $manager = $registry->getManagerForClass($clazz);
            $metaData = $manager->getClassMetadata($clazz);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Cannot get class metadata from object of type %s', $clazz), null, $e);
        }

        $fieldKeys = $metaData->getIdentifier();
        $fieldValues = $metaData->getIdentifierValues($object);

        if (count($fieldKeys) < 1 || count($fieldKeys) !== count($fieldValues)) {
            throw new \InvalidArgumentException(sprintf('Couldn\'t determine identifier for object of type %s; is this managed yet?', $clazz));
        }

        $identifier = array_combine($fieldKeys, $fieldValues);

        // We can't use new self() here as this would break the ObservedEntityReference
        // due to the early binding.
        $self = static::class;

        return new $self($metaData->getName(), $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromReference(ObjectReferenceInterface $objectReference): ObjectReferenceInterface
    {
        return new self(
            $objectReference->getClassName(),
            $objectReference->getIdentifier()
        );
    }

    /**
     * @param $raw
     *
     * @return string
     */
    public static function createSearchKey($raw)
    {
        if (is_array($raw)) {
            asort($raw);
        }

        return sha1(serialize($raw), true);
    }
}
