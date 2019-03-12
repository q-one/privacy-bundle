<?php

namespace QOne\PrivacyBundle\Tests\Unit\Manager;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Delivery\DeliveryInterface;
use QOne\PrivacyBundle\Manager\Collector;
use QOne\PrivacyBundle\Survey\Survey;
use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoUser;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CollectorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Collector::class, $this->getCollector());
    }

    public function testCreateSurvey()
    {
        $collector = $this->getCollector();

        /** @var DeliveryInterface|MockObject $delivery */
        $delivery = $this->createMock(DeliveryInterface::class);
        $collector->addDataSource('foo', $delivery);

        $request = new SurveyRequest(DemoUser::class, ['id' => 1]);
        $this->assertInstanceOf(Survey::class, $collector->createSurvey($request));
    }

    public function getCollector()
    {
        return new Collector();
    }
}
