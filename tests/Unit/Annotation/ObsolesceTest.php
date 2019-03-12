<?php

namespace QOne\PrivacyBundle\Tests\Unit\Annotation;

use InvalidArgumentException;
use QOne\PrivacyBundle\Annotation\Obsolesce;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ObsolesceTest extends TestCase
{
    public function testConstruct()
    {
        $criteriaMock = $this->createMock(ConditionInterface::class);
        $properties = ['group' => 'groupValue', 'criteria' => [$criteriaMock]];
        $obsolesce = new Obsolesce($properties);

        $this->assertInstanceOf(Obsolesce::class, $obsolesce);
        $this->assertSame($obsolesce->getGroup(), 'groupValue');
        $this->assertNull($obsolesce->getPolicy());
        $this->assertNull($obsolesce->getAuditTransformer());
        $this->assertNull($obsolesce->getSource());
        $this->assertTrue($obsolesce->hasAttachments());
    }
    public function testConstructWithInvalidArgumentException()
    {
        $clazz = new \stdClass();
        $this->expectException(InvalidArgumentException::class);
        $properties = ['criteria' => [$clazz]];
        new Obsolesce($properties);
    }
}
