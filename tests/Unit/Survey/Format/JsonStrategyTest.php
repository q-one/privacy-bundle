<?php

namespace QOne\PrivacyBundle\Tests\Unit\Survey\Format;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Survey\Format\JsonStrategy;
use QOne\PrivacyBundle\Survey\SurveyInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class JsonStrategyTest extends TestCase
{
    /**
     * @var SerializerInterface|MockObject
     */
    protected $serializer;

    /**
     * @var SurveyInterface|MockObject
     */
    private $survey;

    /**
     * @var SurveyRequest
     */
    private $surveyRequest;

    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->survey = $this->createMock(SurveyInterface::class);
        $this->surveyRequest = new SurveyRequest();
    }

    public function testEncodeRequest()
    {

        $this->serializer->expects($this->once())->method('serialize')->willReturn('foo');
        $strategy = $this->getStrategy();

        $result = $strategy->encodeRequest($this->surveyRequest);

        $this->assertSame('foo', $result);
    }

    public function testDecodeRequest()
    {
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($this->surveyRequest);
        $strategy = $this->getStrategy();

        $result = $strategy->decodeRequest('foo', $strategy->getMimeType());

        $this->assertInstanceOf(SurveyRequest::class, $result);
    }

    public function testEncodeSurvey()
    {
        $this->serializer->expects($this->once())->method('serialize')->willReturn('foo');
        $strategy = $this->getStrategy();

        $result = $strategy->encodeSurvey($this->survey);

        $this->assertSame('foo', $result);
    }

    public function testDecodeSurvey()
    {
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($this->survey);
        $strategy = $this->getStrategy();

        $result = $strategy->decodeSurvey('foo', $strategy->getMimeType());

        $this->assertInstanceOf(SurveyInterface::class, $result);
    }

    public function testSupportsMimeType()
    {
        $strategy = $this->getStrategy();

        $this->assertTrue($strategy->supportsMimeType('application/json'));
    }

    public function testGetter()
    {
        $strategy = $this->getStrategy();


        $this->assertSame('application/json', $strategy->getMimeType());
    }

    public function getStrategy()
    {
        return new JsonStrategy($this->serializer);
    }
}
