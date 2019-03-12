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

use QOne\PrivacyBundle\Survey\SurveyInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;

/**
 * Class ChainedStrategy.
 */
class ChainedStrategy implements EncodingInterface
{
    /**
     * @var array
     */
    private $strategies;

    /**
     * ChainedStrategy constructor.
     *
     * @param array $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @param SurveyRequest $surveyRequest
     *
     * @return string
     */
    public function encodeRequest(SurveyRequest $surveyRequest): string
    {
        /** @var EncodingInterface $strategy */
        if (null === $strategy = array_shift($this->strategies)) {
            throw new \RuntimeException('No configured encoder.');
        }

        return $strategy->encodeRequest($surveyRequest);
    }

    /**
     * Serializes a Survey into a string.
     *
     * @param SurveyInterface $survey
     *
     * @return string
     */
    public function encodeSurvey(SurveyInterface $survey): string
    {
        /** @var EncodingInterface $strategy */
        if (null === $strategy = array_shift($this->strategies)) {
            throw new \RuntimeException('No configured encoder.');
        }

        return $strategy->encodeSurvey($survey);
    }

    /**
     * @param string $serialized
     * @param string $mimeType
     *
     * @return SurveyRequest
     */
    public function decodeRequest(string $serialized, string $mimeType): SurveyRequest
    {
        /** @var EncodingInterface $decoderStrategy */
        foreach ($this->strategies as $decoderStrategy) {
            if ($decoderStrategy->supportsMimeType($mimeType)) {
                return $decoderStrategy->decodeRequest($serialized, $mimeType);
            }
        }

        throw new \InvalidArgumentException(sprintf('No configured encoder supports mime type "%s"', $mimeType));
    }

    /**
     * Deserializes a Survey from a string.
     *
     * @param string $serialized
     * @param string $mimeType
     *
     * @return SurveyInterface
     */
    public function decodeSurvey(string $serialized, string $mimeType): SurveyInterface
    {
        /** @var EncodingInterface $decoderStrategy */
        foreach ($this->strategies as $decoderStrategy) {
            if ($decoderStrategy->supportsMimeType($mimeType)) {
                return $decoderStrategy->decodeSurvey($serialized, $mimeType);
            }
        }

        throw new \InvalidArgumentException(sprintf('No configured encoder supports mime type "%s"', $mimeType));
    }

    /**
     * @param string $mimeType
     *
     * @return bool
     */
    public function supportsMimeType(string $mimeType): bool
    {
        /** @var EncodingInterface $decoderStrategy */
        foreach ($this->strategies as $decoderStrategy) {
            if ($decoderStrategy->supportsMimeType($mimeType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the mime type.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        /** @var EncodingInterface $strategy */
        if (null === $strategy = array_shift($this->strategies)) {
            throw new \RuntimeException('No configured encoder.');
        }

        return $strategy->getMimeType();
    }
}
