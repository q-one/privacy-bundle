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

use QOne\PrivacyBundle\Manager\Publisher;
use QOne\PrivacyBundle\Manager\PublisherInterface;
use QOne\PrivacyBundle\Survey\FileListInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The LocalDelivery collects the local data from the Publisher.
 */
class LocalDelivery implements DeliveryInterface, DeliveryFactoryInterface
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * LocalDelivery constructor.
     *
     * @param PublisherInterface $publisher
     */
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function collectPrivacyFiles(SurveyRequest $request): FileListInterface
    {
        return $this->publisher
            ->createSurvey($request)
            ->getFiles();
    }

    /**
     * {@inheritdoc}
     */
    public static function createDelivery(ContainerInterface $container, array $configuration): DeliveryInterface
    {
        return $container->get('qone_privacy.delivery.local_delivery');
    }

    /**
     * {@inheritdoc}
     */
    public static function validateDelivery(array $configuration): void
    {
        if (!empty($configuration)) {
            throw new \InvalidArgumentException(sprintf(
                '%s does not allow any configuration options; got %s',
                self::class,
                implode(', ', array_keys($configuration))
            ));
        }
    }
}
