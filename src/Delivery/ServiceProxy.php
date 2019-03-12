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

namespace QOne\PrivacyBundle\Delivery;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceProxy implements DeliveryFactoryInterface
{
    /**
     * ServiceProxy constructor.
     */
    private function __construct()
    {
        // You ... shall not ... construct.
    }

    /**
     * {@inheritdoc}
     */
    public static function createDelivery(ContainerInterface $container, array $configuration): DeliveryInterface
    {
        $delivery = $container->get($configuration['id'], ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);

        if (!$delivery instanceof DeliveryInterface) {
            throw new \RuntimeException(sprintf(
                'Expected "%s" service to be an instance of %s, but got %s',
                $configuration['id'], DeliveryInterface::class, get_class($delivery)
            ));
        }

        return $delivery;
    }

    /**
     * @param array $configuration
     *
     * @throws \InvalidArgumentException
     */
    public static function validateDelivery(array $configuration): void
    {
        if (!isset($configuration['id'])) {
            throw new \InvalidArgumentException(sprintf('%s expected the id attribute to be a service name', self::class));
        }

        // simple but effective
        unset($configuration['id']);

        if (!empty($configuration)) {
            throw new \InvalidArgumentException(sprintf(
                '%s does not allow any configuration options other than "id"; got %s',
                self::class,
                implode(', ', array_keys($configuration))
            ));
        }
    }
}
