<?php

namespace QOne\PrivacyBundle\Tests\Unit\Survey\Format;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Survey\Format\ChainedStrategy;
use QOne\PrivacyBundle\Survey\Format\EncodingInterface;
use QOne\PrivacyBundle\Survey\Format\JsonStrategy;
use QOne\PrivacyBundle\Survey\Survey;
use QOne\PrivacyBundle\Survey\SurveyInterface;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoUser;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class ChainedStrategyTest extends TestCase
{

    /** @var SerializerInterface|MockObject */
    protected $serializer;

    /** @var JsonStrategy|MockObject $mock */
    protected $jsonStrategy;

    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->jsonStrategy = $this->createMock(JsonStrategy::class);
    }

    public function testGetMimeType()
    {
        $this->jsonStrategy->expects($this->once())->method('getMimeType')->willReturn('application/json');
        $strategy = $this->getChainedStrategy();

        $this->assertSame('application/json', $strategy->getMimeType());
    }

    public function testGetMimeTypeWithRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $strategy = new ChainedStrategy([]);

        $strategy->getMimeType();
    }

    public function testSupportsMimeTypeOnFalse()
    {
        $strategy = $this->getChainedStrategy();

        $this->assertFalse($strategy->supportsMimeType('mimeTypeFoo'));
    }

    public function testSupportsMimeTypeOnTrue()
    {
        $strategy = $this->getChainedStrategy();
        $this->jsonStrategy->expects($this->once())->method('supportsMimeType')->willReturn(true);
        $this->assertTrue($strategy->supportsMimeType('application/json'));
    }

    /**
     * @throws \Exception
     */
    public function testEncodeRequest()
    {
        $surveyRequest = new SurveyRequest(DemoUser::class, ['id' => 1]);

        $this->jsonStrategy->expects($this->once())->method('encodeRequest')->willReturn(json_encode($surveyRequest));

        $strategy = $this->getChainedStrategy();
        $decoded = $strategy->encodeRequest($surveyRequest);

        $this->assertSame(json_encode($surveyRequest), $decoded);
    }
    /**
     * @throws \Exception
     */
    public function testEncodeRequestWithRuntimeException()
    {
        $this->expectException(RuntimeException::class);

        $surveyRequest = new SurveyRequest(DemoUser::class, ['id' => 1]);
        $this->jsonStrategy = null;

        $strategy = $this->getChainedStrategy();
        $strategy->encodeRequest($surveyRequest);
    }

    /**
     * @throws \Exception
     */
    public function testEncodeSurvey()
    {
        $survey = $this->getSurvey();

        $this->jsonStrategy->expects($this->once())->method('encodeSurvey')->willReturn(json_encode($survey));

        $strategy = $this->getChainedStrategy();
        $decoded = $strategy->encodeSurvey($survey);

        $this->assertSame(json_encode($survey), $decoded);
    }

    /**
     * @throws \Exception
     */
    public function testEncodeSurveyWithRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $this->jsonStrategy = null;
        $survey = $this->getSurvey();

        $strategy = $this->getChainedStrategy();
        $strategy->encodeSurvey($survey);
    }

    /**
     * @throws \Exception
     */
    public function testDecodeRequest()
    {
        $surveyRequest = new SurveyRequest(DemoUser::class, ['id' => 1]);

        $this->jsonStrategy->expects($this->once())->method('supportsMimeType')->willReturn(true);
        $this->jsonStrategy->expects($this->once())->method('decodeRequest')->willReturn($surveyRequest);

        $strategy = $this->getChainedStrategy();
        $decoded = $strategy->decodeRequest(json_encode($surveyRequest), 'application/json');

        $this->assertSame($surveyRequest, $decoded);
    }

    /**
     * @throws \Exception
     */
    public function testDecodeRequestWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $surveyRequest = new SurveyRequest(DemoUser::class, ['id' => 1]);

        $this->jsonStrategy->expects($this->once())->method('supportsMimeType')->willReturn(false);
        $this->jsonStrategy->expects($this->never())->method('decodeRequest')->willReturn($surveyRequest);

        $strategy = $this->getChainedStrategy();
        $strategy->decodeRequest(json_encode($surveyRequest), 'application/json');
    }

    /**
     * @throws \Exception
     */
    public function testDecodeSurvey()
    {
        $survey = $this->getSurvey();

        $this->jsonStrategy->expects($this->once())->method('supportsMimeType')->willReturn(true);
        $this->jsonStrategy->expects($this->once())->method('decodeSurvey')->willReturn($survey);

        $strategy = $this->getChainedStrategy();
        $decoded = $strategy->decodeSurvey(json_encode($survey), 'application/json');

        $this->assertSame($survey, $decoded);
    }


    /**
     * @throws \Exception
     */
    public function testDecodeSurveyWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $strategy = $this->getChainedStrategy();

        $strategy->decodeSurvey('{}', 'application/json');
    }

    public function getChainedStrategy(): EncodingInterface
    {
        return new ChainedStrategy([$this->jsonStrategy]);
    }

    /**
     * @return Survey
     * @throws \Exception
     */
    private function getSurvey(): SurveyInterface
    {
        /** @var ObjectReferenceInterface|MockObject $entityReference */
        $entityReference = $this->createMock(ObjectReferenceInterface::class);
        return new Survey($entityReference);
    }
}