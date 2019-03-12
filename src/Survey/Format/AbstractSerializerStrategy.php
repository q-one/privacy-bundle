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

namespace QOne\PrivacyBundle\Survey\Format;

use QOne\PrivacyBundle\Survey\Survey;
use QOne\PrivacyBundle\Survey\SurveyInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handles the serialization and deserialization of a survey
 * in JSON encoding.
 */
abstract class AbstractSerializerStrategy implements EncodingInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function encodeRequest(SurveyRequest $surveyRequest): string
    {
        return $this->serializer->serialize(
            $surveyRequest,
            $this->getSerializerFormat()
        );
    }

    public function encodeSurvey(SurveyInterface $survey): string
    {
        return $this->serializer->serialize(
            $survey,
            $this->getSerializerFormat()
        );
    }

    public function decodeRequest(string $serialized, string $mimeType): SurveyRequest
    {
        return $this->serializer->deserialize(
            $serialized,
            SurveyRequest::class,
            $this->getSerializerFormat()
        );
    }

    public function decodeSurvey(string $serialized, string $mimeType): SurveyInterface
    {
        return $this->serializer->deserialize(
            $serialized,
            SurveyInterface::class,
            $this->getSerializerFormat()
        );
    }

    public function supportsMimeType(string $mimeType = null): bool
    {
        return $mimeType === $this->getMimeType();
    }

    abstract protected function getSerializerFormat();
}
