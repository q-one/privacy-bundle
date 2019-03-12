<?php

namespace QOne\PrivacyBundle\Tests\Unit\Annotation;

use InvalidArgumentException;
use QOne\PrivacyBundle\Annotation\Audited;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AuditedTest extends TestCase
{
    public function testConstruct()
    {
        $audited = new Audited(['user' => 'foo']);

        $this->assertInstanceOf(Audited::class, $audited);
        $this->assertSame('foo', $audited->getUser());
    }

    public function testConstructException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Audited(['invalidUserKey' => 'foo']);
    }
}
