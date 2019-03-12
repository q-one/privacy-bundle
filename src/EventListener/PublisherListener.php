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

namespace QOne\PrivacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class PublisherListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $publisherPath;

    /**
     * @var string
     */
    private $publisherController;

    /**
     * @param string $publisherPath
     * @param string $publisherController
     */
    public function __construct(string $publisherPath, string $publisherController)
    {
        $this->publisherPath = $publisherPath;
        $this->publisherController = $publisherController;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_controller')
        || $request->getPathInfo() !== $this->publisherPath) {
            return;
        }

        if (Request::METHOD_POST !== $request->getMethod()) {
            throw new MethodNotAllowedHttpException(
                [Request::METHOD_POST],
                sprintf('Method %s not allowed for privacy publisher at %s; expected POST', $request->getMethod(), $this->publisherPath)
            );
        }

        $request->attributes->set(
            '_controller',
            $this->publisherController
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // RouterListener has priority 32, so this one needs to run right before it
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 35]],
        ];
    }
}
