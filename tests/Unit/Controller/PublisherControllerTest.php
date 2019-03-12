<?php

namespace QOne\PrivacyBundle\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Controller\PublisherController;
use QOne\PrivacyBundle\Manager\CollectorInterface;
use QOne\PrivacyBundle\Manager\Publisher;
use QOne\PrivacyBundle\Manager\PublisherInterface;
use QOne\PrivacyBundle\Persistence\Object\SimpleReference;
use QOne\PrivacyBundle\Survey\Format\EncodingInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoUser;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublisherControllerTest extends TestCase
{
    /** @var PublisherInterface|MockObject */
    protected $publisher;

    /** @var CollectorInterface|MockObject */
    protected $collector;

    /** @var Request|MockObject */
    protected $request;

    /** @var EncodingInterface|MockObject */
    protected $encoder;

    protected function setUp()
    {
        parent::setUp();
        $this->publisher = $this->createMock(PublisherInterface::class);
        $this->collector = $this->createMock(CollectorInterface::class);

        $this->encoder = $this->createMock(EncodingInterface::class);

        $this->request = $this->createMock(Request::class);

    }

    public function testInvoke()
    {
        $this->request->expects($this->once())->method('getContent')->willReturn('contentFoo');
        $this->request->expects($this->once())->method('getContentType')->willReturn('text/json');

        $surveyRequest = new SurveyRequest(DemoUser::class, ['id' => 1]);
        $this->encoder->expects($this->once())->method('decodeRequest')->willReturn($surveyRequest);

        $this->publisher->expects($this->once())->method('getEncodingStrategy')->willReturn($this->encoder);
        $this->assertInstanceOf(SimpleReference::class, $surveyRequest->createUserReference());

        $publisherController = $this->getPublisherController();
        $publisherController($this->request);
    }

    public function testInvokeBadRequestHttpExceptionByUser()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->request->expects($this->once())->method('getContent')->willReturn('contentFoo');
        $this->request->expects($this->once())->method('getContentType')->willReturn('text/json');

        $surveyRequest = new SurveyRequest(null, []);
        $this->encoder->expects($this->once())->method('decodeRequest')->willReturn($surveyRequest);

        $this->publisher->expects($this->once())->method('getEncodingStrategy')->willReturn($this->encoder);

        $publisherController = $this->getPublisherController();
        $publisherController($this->request);
    }

    public function testInvokeBadRequestHttpExceptionByUserIdentifier()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->request->expects($this->once())->method('getContent')->willReturn('contentFoo');
        $this->request->expects($this->once())->method('getContentType')->willReturn('text/json');

        $surveyRequest = new SurveyRequest(DemoUser::class, []);
        $this->encoder->expects($this->once())->method('decodeRequest')->willReturn($surveyRequest);

        $this->publisher->expects($this->once())->method('getEncodingStrategy')->willReturn($this->encoder);

        $publisherController = $this->getPublisherController();
        $publisherController($this->request);
    }

    public function getPublisherController()
    {
        return new PublisherController($this->publisher, $this->collector);
    }
}
