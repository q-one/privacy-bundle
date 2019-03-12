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

namespace QOne\PrivacyBundle\Controller;

use QOne\PrivacyBundle\Manager\CollectorInterface;
use QOne\PrivacyBundle\Manager\PublisherInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class PublisherController.
 */
class PublisherController extends Controller
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var CollectorInterface
     */
    private $collector;

    /**
     * PublisherController constructor.
     *
     * @param PublisherInterface $publisher
     * @param CollectorInterface $collector
     */
    public function __construct(PublisherInterface $publisher, CollectorInterface $collector)
    {
        $this->publisher = $publisher;
        $this->collector = $collector;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $encoder = $this->publisher->getEncodingStrategy();

        /** @var SurveyRequest $surveyRequest */
        $surveyRequest = $encoder->decodeRequest($request->getContent(), $request->getContentType());

        if (empty($surveyRequest->getUserClass())) {
            throw new BadRequestHttpException('Property of userClass must not be empty');
        } elseif (empty($surveyRequest->getUserIdentifier())) {
            throw new BadRequestHttpException('Property of userIdentifier must not be empty');
        }

        $survey = $this->collector->createSurvey($surveyRequest);

        return new Response(
            $encoder->encodeSurvey($survey),
            Response::HTTP_OK,
            [
               'Content-Type' => $encoder->getMimeType(),
            ]
        );
    }
}
