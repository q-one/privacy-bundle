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

namespace QOne\PrivacyBundle\Persistence\Object;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class SimpleReference.
 */
class SimpleReference implements ObjectReferenceInterface
{
    use ObjectReferenceTrait;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $identifier;

    /**
     * @param string $className
     * @param array  $identifier
     */
    public function __construct(string $className, array $identifier)
    {
        $this->className = $className;
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): array
    {
        return $this->identifier;
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
        throw new \BadMethodCallException('Not implemented.');
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
}
