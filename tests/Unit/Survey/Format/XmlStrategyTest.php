<?php

namespace QOne\PrivacyBundle\Tests\Unit\Survey\Format;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Survey\Format\XmlStrategy;
use QOne\PrivacyBundle\Survey\SurveyInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class XmlStrategyTest extends TestCase
{
    /**
     * @var SerializerInterface|MockObject
     */
    protected $serializer;

    /**
     * @var SurveyInterface|MockObject
     */
    private $survey;

    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->survey = $this->createMock(SurveyInterface::class);
    }


    public function testEncodeSurvey()
    {
        $this->serializer->expects($this->once())->method('serialize')->willReturn('foo');
        $strategy = $this->getStrategy();

        $result = $strategy->encodeSurvey($this->survey);

        $this->assertSame('foo', $result);
    }

    public function testGetter()
    {
        $xml = $this->getStrategy();

        $this->assertSame('text/xml', $xml->getMimeType());

    }

    public function getStrategy()
    {
        return new XmlStrategy($this->serializer);
    }
}
