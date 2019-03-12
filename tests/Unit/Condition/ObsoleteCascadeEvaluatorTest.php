<?php

namespace QOne\PrivacyBundle\Tests\Unit\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Condition\ObsoleteCascade;
use QOne\PrivacyBundle\Condition\ObsoleteCascadeEvaluator;
use QOne\PrivacyBundle\Exception\EvaluationException;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ObsoleteCascadeEvaluatorTest extends TestCase
{
    /** @var ManagerRegistry|MockObject */
    protected $managerRegistry;

    /** @var ExpressionLanguage|MockObject */
    protected $expressionLanguage;

    /** @var ObsoleteCascade|MockObject */
    protected $condition;

    /** @var ObservedObjectReferenceInterface|MockObject */
    protected $observedObjectReference;

    /** @var \stdClass|MockObject */
    protected $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->condition = $this->createMock(ObsoleteCascade::class);
        $this->observedObjectReference = $this->createMock(ObservedObjectReferenceInterface::class);

        $this->subject = $this->createMock(\stdClass::class);
    }

    public function testEvaluateOnJudgeObsolete()
    {
        $this->expressionLanguage->expects($this->once())->method('evaluate')->willReturn(null);
        $evaluator = $this->getEvaluator();

        $result = $evaluator->evaluate($this->subject, $this->observedObjectReference, $this->condition);

        $this->assertSame(VerdictInterface::JUDGE_OBSOLETE, $result);
    }

    /**
     * @throws EvaluationException
     */
    public function testEvaluateWithEvaluationException()
    {
        $this->expectException(EvaluationException::class);
        $this->expressionLanguage->expects($this->once())->method('evaluate')->willReturn(true);
        $evaluator = $this->getEvaluator();

        $evaluator->evaluate($this->subject, $this->observedObjectReference, $this->condition);
    }

    /**
     * @throws EvaluationException
     */
    public function testEvaluateAsResultIsObject()
    {
        $rm = $this->createMock(ObjectManager::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->expects($this->once())->method('getIdentifierValues')->willReturn(null);
        $rm->expects($this->once())->method('getClassMetadata')->willReturn($classMetadata);
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')->willReturn($rm);

        $this->expressionLanguage->expects($this->once())->method('evaluate')->willReturn(new \stdClass());
        $evaluator = $this->getEvaluator();

        $result = $evaluator->evaluate($this->subject, $this->observedObjectReference, $this->condition);

        $this->assertSame(VerdictInterface::JUDGE_OBSOLETE, $result);
    }
    /**
     * @throws EvaluationException
     */
    public function testEvaluateAsAppropriate()
    {
        $rm = $this->createMock(ObjectManager::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->expects($this->once())->method('getIdentifierValues')->willReturn(null);
        $rm->expects($this->once())->method('getClassMetadata')->willReturn($classMetadata);
        $rm->expects($this->once())->method('find')->willReturn(true);
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')->willReturn($rm);

        $this->expressionLanguage->expects($this->once())->method('evaluate')->willReturn(new \stdClass());
        $evaluator = $this->getEvaluator();

        $result = $evaluator->evaluate($this->subject, $this->observedObjectReference, $this->condition);

        $this->assertSame(VerdictInterface::JUDGE_APPROPRIATE, $result);
    }

    /**
     * @throws EvaluationException
     */
    public function testEvaluateWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $evaluator = $this->getEvaluator();

        /** @var ConditionInterface|MockObject $condition */
        $condition = $this->createMock(ConditionInterface::class);
        $evaluator->evaluate($this->subject, $this->observedObjectReference, $condition);

    }

    public function getEvaluator()
    {
        return new ObsoleteCascadeEvaluator($this->managerRegistry, $this->expressionLanguage);
    }
}
