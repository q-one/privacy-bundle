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

use GuzzleHttp\Client;
use QOne\PrivacyBundle\Survey\Format\EncodingInterface;
use QOne\PrivacyBundle\Survey\FileListInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The HttpDelivery uses the GuzzleHttp to get a Survey from an http path.
 */
class HttpDelivery implements DeliveryInterface, DeliveryFactoryInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var EncodingInterface
     */
    private $encodingStrategy;

    /**
     * @var string
     */
    protected $path;

    /**
     * HttpDelivery constructor.
     *
     * @param Client            $client
     * @param EncodingInterface $encodingStrategy
     * @param string            $path
     */
    public function __construct(Client $client, EncodingInterface $encodingStrategy, string $path)
    {
        $this->client = $client;
        $this->encodingStrategy = $encodingStrategy;
        $this->path = $path;
    }

    /**
     * @return EncodingInterface
     */
    public function getEncodingStrategy(): EncodingInterface
    {
        return $this->encodingStrategy;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function collectPrivacyFiles(SurveyRequest $request): FileListInterface
    {
        $request = $this->getEncodingStrategy()->encodeRequest(
            new SurveyRequest($request->getUserClass(), $request->getUserIdentifier())
        );
        $requestContentType = $this->getEncodingStrategy()->getMimeType();

        $response = $this->client->request(
            'POST',
            $this->path,
            [
                'body' => $request,
                'headers' => [
                    'Content-Type' => $requestContentType,
                ],
            ]
        );

        $contentType = $response->getHeader('Content-Type');
        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (Response::HTTP_OK === $statusCode) {
            if ($this->encodingStrategy->supportsMimeType($contentType)) {
                $survey = $this->encodingStrategy->decodeSurvey($responseBody, $contentType);

                return $survey->getFiles();
            } else {
                throw new \RuntimeException(sprintf('EncodingInterface does not support the mime type "%s"', $contentType));
            }
        } else {
            throw new \RuntimeException(sprintf('Getting status code "%s"', $statusCode));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function createDelivery(ContainerInterface $container, array $configuration): DeliveryInterface
    {
        if (isset($configuration['http_client'])) {
            /** @var \GuzzleHttp\Client $client */
            $client = $container->get($configuration['http_client'], ContainerInterface::NULL_ON_INVALID_REFERENCE);

            if (!$client instanceof Client) {
                throw new \RuntimeException(sprintf('Given http_client "%s" is not an instance of \\GuzzleHttp\\Client!', $configuration['http_client']));
            }
        } else {
            $guzzleConfig = array_filter($configuration, function ($v, $k) {
                return !in_array($k, [
                    'http_client', 'http_path', 'http_encoding_strategy',
                ]);
            }, ARRAY_FILTER_USE_BOTH);
            $client = new Client($guzzleConfig);
        }

        $encodingStrategy = $container->get(
            $configuration['http_encoding_strategy'] ?? 'qone_privacy.publisher.encoding_strategy',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );

        if (!$encodingStrategy instanceof EncodingInterface) {
            throw new \RuntimeException(sprintf('Expected given http_encoding_strategy "%s" to be a valid encoding strategy implementing %s', $configuration['http_encoding_strategy'], EncodingInterface::class));
        }

        return new HttpDelivery($client, $encodingStrategy, $configuration['http_path']);
    }

    /**
     * {@inheritdoc}
     */
    public static function validateDelivery(array $configuration): void
    {
        if (empty($configuration['http_path'])) {
            throw new \InvalidArgumentException(sprintf('%s expected "http_path" to be defined', self::class));
        }

        if (empty($configuration['http_client'])) {
            // there is no GuzzleClient supplied; plausibility check of the configuration options
            if (empty($configuration['base_uri'])) {
                throw new \InvalidArgumentException('If you do not supply the "http_client" attribute you must at leaste specify a "base_uri" attribute.');
            }
        } else {
            unset($configuration['http_client'], $configuration['http_path'], $configuration['http_encoding_strategy']);

            if (!empty($configuration)) {
                throw new \InvalidArgumentException(sprintf(
                    'If you specify "http_client", %s does not allow any configuration options other than "http_path" and "http_encoding_strategy"; got %s',
                    self::class,
                    implode(', ', array_keys($configuration))
                ));
            }
        }
    }
}
