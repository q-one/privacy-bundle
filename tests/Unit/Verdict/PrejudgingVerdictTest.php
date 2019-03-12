<?php

namespace QOne\PrivacyBundle\Tests\Unit\Verdict;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Verdict\PrejudgingVerdict;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use UnexpectedValueException;

class PrejudgingVerdictTest extends TestCase
{
    /** @var object */
    protected $object;

    /** @var ClassMetadataInterface|MockObject */
    protected $classMetadata;

    /** @var GroupMetadataInterface|MockObject */
    protected $groupMetadata;

    /** @var array */
    protected $votes;

    /** @var ConditionInterface|MockObject */
    protected $condition;

    protected function setUp()
    {
        parent::setUp();

        $this->object = (object)[];
        $this->classMetadata = $this->createMock(ClassMetadataInterface::class);
        $this->groupMetadata = $this->createMock(GroupMetadataInterface::class);

        $this->condition = $this->createMock(ConditionInterface::class);
        $this->votes = [
            VerdictInterface::JUDGE_OBSOLETE => [$this->condition],
            VerdictInterface::JUDGE_APPROPRIATE => [$this->condition],
            VerdictInterface::JUDGE_KEEP => [$this->condition],
        ];
    }

    public function testConstructUnexpectedValueException()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->votes[] = [new \stdClass()];
        new PrejudgingVerdict($this->object, $this->classMetadata, $this->groupMetadata, $this->votes);
    }

    public function testIsObsolete()
    {
        $verdict = $this->getVerdict();

        $this->assertTrue($verdict->isObsolete());
    }

    public function testGetObject()
    {
        $verdict = $this->getVerdict();

        $this->assertIsObject($verdict->getObject());
    }

    public function testGetClassMetadata()
    {
        $verdict = $this->getVerdict();

        $this->assertInstanceOf(ClassMetadataInterface::class, $verdict->getClassMetadata());
    }

    public function testGetGroupMetadata()
    {
        $verdict = $this->getVerdict();

        $this->assertInstanceOf(GroupMetadataInterface::class, $verdict->getGroupMetadata());
    }

    public function testGetAppropriateVotes()
    {
        $verdict = $this->getVerdict();

        $this->assertIsArray($verdict->getAppropriateVotes());
        $this->assertInstanceOf(ConditionInterface::class, $verdict->getAppropriateVotes()[0]);
    }

    public function testGetObsoleteVotes()
    {
        $verdict = $this->getVerdict();

        $this->assertIsArray($verdict->getObsoleteVotes());
        $this->assertInstanceOf(ConditionInterface::class, $verdict->getObsoleteVotes()[0]);
    }

    public function testGetKeepVotes()
    {
        $verdict = $this->getVerdict();

        $this->assertIsArray($verdict->getKeepVotes());
        $this->assertInstanceOf(ConditionInterface::class, $verdict->getKeepVotes()[0]);
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            VerdictInterface::class,
            PrejudgingVerdict::create($this->object, $this->classMetadata, $this->groupMetadata)
        );
    }

    public function testAddVote()
    {
        $this->expectException(InvalidArgumentException::class);
        $verdict = $this->getVerdict();
        $verdict->addVote($this->condition, 999);
    }

    public function getVerdict()
    {
        return new PrejudgingVerdict($this->object, $this->classMetadata, $this->groupMetadata, $this->votes);
    }
}
