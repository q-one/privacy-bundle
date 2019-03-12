<?php

namespace QOne\PrivacyBundle\Tests\Unit\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Condition\ObsoleteAfter;
use QOne\PrivacyBundle\Condition\ObsoleteAfterEvaluator;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObsoleteAfterEvaluatorTest extends TestCase
{
    /** @var ManagerRegistry|MockObject */
    protected $managerRegistry;

    /** @var ObsoleteAfter|MockObject */
    protected $condition;

    /** @var ObservedObjectReferenceInterface|MockObject */
    protected $observedObjectReference;

    /** @var string */
    protected $interval;

    /** @var array */
    protected $refresh;

    /** @var string */
    protected $message;

    /** @var \stdClass|MockObject */
    protected $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->interval = 'PT15M';
        $this->refresh = [];
        $this->message = 'This is a condition message';

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->observedObjectReference = $this->createMock(ObservedObjectReferenceInterface::class);

        $this->subject = $this->createMock(\stdClass::class);
    }

    /**
     * @throws \Exception
     */
    public function testEvaluateWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $evaluator = $this->getEvaluator();
        /** @var ConditionInterface|MockObject $condition */
        $condition = $this->createMock(ConditionInterface::class);
        $evaluator->evaluate($this->subject, $this->observedObjectReference, $condition);
    }

    /**
     * @throws \Exception
     */
    public function testEvaluate()
    {
        $evaluator = $this->getEvaluator();
        $this->refresh = ['create', 'update'];

        $this->observedObjectReference->expects($this->once())->method('getCreatedAt')->willReturn(new \DateTime());
        $this->observedObjectReference->expects($this->exactly(2))->method('getUpdatedAt')->willReturn(new \DateTime());

        $status = $evaluator->evaluate($this->subject, $this->observedObjectReference, $this->getCondition());
        $this->assertIsInt($status);
    }

    /**
     * @throws \Exception
     */
    public function testPredict()
    {
        $evaluator = $this->getEvaluator();
        $status = $evaluator->predict($this->subject, $this->observedObjectReference, $this->getCondition());

        $this->assertInstanceOf(\DateInterval::class, $status);
    }

    public function getCondition()
    {
        return new ObsoleteAfter(
            [
                'interval' => $this->interval,
                'refresh' => $this->refresh,
                'message' => $this->message
            ]
        );
    }

    public function getEvaluator()
    {
        /** @var ObsoleteAfterEvaluator|MockObject $evaluator */
        $evaluator = $this->getMockBuilder(ObsoleteAfterEvaluator::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->setMethods(['getInterval'])->getMock();

        return $evaluator;
    }
}
