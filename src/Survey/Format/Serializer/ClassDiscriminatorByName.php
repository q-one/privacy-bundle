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

namespace QOne\PrivacyBundle\Survey\Format\Serializer;

use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;

class ClassDiscriminatorByName implements ClassDiscriminatorResolverInterface
{
    /**
     * @var array
     */
    private $whitelist;

    /**
     * @var bool
     */
    private $subclassesAllowed;

    public function __construct(array $whitelist, bool $subclassesAllowed = false)
    {
        $this->whitelist = $whitelist;
        $this->subclassesAllowed = $subclassesAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingForClass(string $class): ?ClassDiscriminatorMapping
    {
        if ($this->isWhitelisted($class)) {
            return new IdentityClassDiscriminatorMapping();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingForMappedObject($object): ?ClassDiscriminatorMapping
    {
        if ($type = $this->getTypeForMappedObject($object)) {
            return $this->getMappingForClass($type);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeForMappedObject($object): ?string
    {
        $class = get_class($object);

        return $this->isWhitelisted($class) ? $class : null;
    }

    private function isWhitelisted(string $class)
    {
        if (in_array($class, $this->whitelist)) {
            return true;
        }

        if ($this->subclassesAllowed) {
            foreach ($this->whitelist as $whitelistedClass) {
                if (is_a($class, $whitelistedClass, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
