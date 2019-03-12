<?php

namespace QOne\PrivacyBundle\Tests\Unit\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Condition\JudgementMapInterface;
use QOne\PrivacyBundle\Condition\ObsoleteCascade;
use QOne\PrivacyBundle\Condition\ObsoleteExprEvaluator;
use QOne\PrivacyBundle\Exception\PrivacyException;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ObsoleteExprEvaluatorTest extends TestCase
{
    /** @var ManagerRegistry|MockObject */
    protected $managerRegistry;

    /** @var ExpressionLanguage|MockObject */
    protected $expressionLanguage;


    /** @var ConditionInterface|MockObject */
    protected $conditionInterface;

    /** @var ObservedObjectReferenceInterface|MockObject */
    protected $objectReference;

    /** @var \stdClass|MockObject */
    protected $subject;

    /** @var ConditionInterface|MockObject */
    protected $condition;

    protected function setUp()
    {
        parent::setUp();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->conditionInterface = $this->createMock(ConditionInterface::class);
        $this->condition = $this->createMock(TempCondition::class);
        $this->objectReference = $this->createMock(ObservedObjectReferenceInterface::class);

        $this->subject = $this->createMock(\stdClass::class);
    }

    public function testEvaluateWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $evaluator = $this->getEvaluator();
        $evaluator->evaluate($this->managerRegistry, $this->objectReference, $this->conditionInterface);
    }

    public function testEvaluateDefaultJudgement()
    {
        $this->condition->expects($this->once())->method('getExpr')->willReturn('foo');
        $evaluator = $this->getEvaluator();
        $evaluator->evaluate($this->managerRegistry, $this->objectReference, $this->condition);
    }

    public function testEvaluate()
    {
        $this->condition->expects($this->once())->method('getExpr')->willReturn('foo');
        $this->expressionLanguage->expects($this->once())->method('evaluate')->willReturn('fooKey');
        $this->condition->expects($this->once())->method('getJudgementMap')->willReturn(['fooKey' => 1]);
        $evaluator = $this->getEvaluator();
        $evaluator->evaluate($this->managerRegistry, $this->objectReference, $this->condition);
    }

    public function getEvaluator()
    {
        return new ObsoleteExprEvaluator($this->managerRegistry, $this->expressionLanguage);
    }
}

class TempCondition implements ConditionInterface, JudgementMapInterface
{

    /**
     * Returns the service id of the Evaluator of this Condition.
     *
     * @return string
     */
    public function evaluatedBy(): string
    {
        // TODO: Implement evaluatedBy() method.
    }

    /**
     * Returns the raw message regarding the cause of obsoletion.
     * Contains placeholders for the parameters returned by {@link getMessageParameters}.
     *
     * @return string
     */
    public function getMessageTemplate(): string
    {
        // TODO: Implement getMessageTemplate() method.
    }

    /**
     * Returns the parameters for the message template.
     *
     * @return array
     */
    public function getMessageParameters(): array
    {
        // TODO: Implement getMessageParameters() method.
    }

    /**
     * @return int
     */
    public function getDefaultJudgement(): int
    {
        // TODO: Implement getDefaultJudgement() method.
    }

    /**
     * @return array
     */
    public function getJudgementMap(): array
    {
        // TODO: Implement getJudgementMap() method.
    }

    public function getExpr()
    {

    }
}
