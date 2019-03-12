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

namespace QOne\PrivacyBundle\Tests\Unit\Delivery;

use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use QOne\PrivacyBundle\Delivery\HttpDelivery;
use QOne\PrivacyBundle\Survey\FileListInterface;
use QOne\PrivacyBundle\Survey\Format\EncodingInterface;
use QOne\PrivacyBundle\Survey\Survey;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class HttpDeliveryTest extends TestCase
{
    /**
     * @var EncodingInterface|MockObject
     */
    protected $encodingStrategy;

    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var ContainerInterface|MockObject
     */
    protected $container;

    /**
     * @var EncodingInterface
     */
    protected $encoding;

    protected function setUp()
    {
        $this->encodingStrategy = $this->createMock(EncodingInterface::class);
        $this->client = $this->createMock(Client::class);
        $this->path = '/_test';
        $this->container = $this->createMock(ContainerInterface::class);
        $this->configuration = [
            'http_client' => '',
            'http_path' => '',
            'http_encoding_strategy' => 'QOne\\PrivacyBundle\\Survey\\Format\\JsonStrategy',
        ];
        $this->encoding = $this->createMock(EncodingInterface::class);
    }

    public function testGetEncodingStrategy()
    {
        $delivery = $this->getDelivery();

        $this->assertInstanceOf(EncodingInterface::class, $delivery->getEncodingStrategy());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCollectPrivacyFiles()
    {
        $delivery = $this->getDelivery();
        $request = new SurveyRequest();

        $response = $this->createMock(ResponseInterface::class);
        $survey = $this->createMock(Survey::class);
        $fileList = $this->createMock(FileListInterface::class);

        $this->encodingStrategy->expects($this->once())->method('getMimeType')->willReturn('application/json');
        $this->client->expects($this->once())->method('request')->willReturn($response);
        $response->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn('application/json');
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn('');
        $this->encodingStrategy->expects($this->once())->method('supportsMimeType')->with('application/json')->willReturn(true);
        $this->encodingStrategy->expects($this->once())->method('decodeSurvey')->willReturn($survey);
        $survey->expects($this->once())->method('getFiles')->willReturn($fileList);

        $this->assertInstanceOf(FileListInterface::class, $delivery->collectPrivacyFiles($request));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCollectPrivacyFilesExceptionMimeType()
    {
        $this->expectException(\RuntimeException::class);

        $delivery = $this->getDelivery();
        $request = new SurveyRequest();
        $response = $this->createMock(ResponseInterface::class);

        $this->encodingStrategy->expects($this->once())->method('getMimeType')->willReturn('application/json');
        $this->client->expects($this->once())->method('request')->willReturn($response);
        $response->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn('application/json');
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn('');
        $this->encodingStrategy->expects($this->once())->method('supportsMimeType')->with('application/json')->willReturn(false);

        $delivery->collectPrivacyFiles($request);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCollectPrivacyFilesExceptionStatusCode()
    {
        $this->expectException(\RuntimeException::class);

        $delivery = $this->getDelivery();
        $request = new SurveyRequest();
        $response = $this->createMock(ResponseInterface::class);

        $this->encodingStrategy->expects($this->once())->method('getMimeType')->willReturn('application/json');
        $this->client->expects($this->once())->method('request')->willReturn($response);
        $response->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn('application/json');
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $delivery->collectPrivacyFiles($request);
    }

    public function testCreateDelivery()
    {
        $this->container->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(
            $this->client,
            $this->encoding
        );

        $delivery = HttpDelivery::createDelivery($this->container, $this->configuration);

        $this->assertInstanceOf(HttpDelivery::class, $delivery);
    }

    public function testCreateDeliveryExceptionClient()
    {
        $this->expectException(\RuntimeException::class);

        $this->container->expects($this->once())->method('get')->willReturn(new \stdClass());

        HttpDelivery::createDelivery($this->container, $this->configuration);
    }

    public function testCreateDeliveryWithoutConfig()
    {
        unset($this->configuration['http_client']);

        $this->container->expects($this->once())->method('get')->willReturn(
            $this->encoding
        );

        $delivery = HttpDelivery::createDelivery($this->container, $this->configuration);

        $this->assertInstanceOf(HttpDelivery::class, $delivery);
    }

    public function testCreateDeliveryExceptionEncoding()
    {
        $this->expectException(\RuntimeException::class);

        $this->encoding = new \stdClass();

        $this->container->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(
            $this->client,
            $this->encoding
        );

        HttpDelivery::createDelivery($this->container, $this->configuration);
    }

    public function testValidateDelivery()
    {
        $this->assertTrue(true);
    }

    private function getDelivery()
    {
        return new HttpDelivery($this->client, $this->encodingStrategy, $this->path);
    }
}
