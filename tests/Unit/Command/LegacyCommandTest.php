<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
 * This file is part of QOnePrivacyBundle.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace QOne\PrivacyBundle\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use QOne\PrivacyBundle\Command\LegacyCommand;
use QOne\PrivacyBundle\Manager\PrivacyManagerInterface;
use QOne\PrivacyBundle\Obsolescence\LegacyResult;
use QOne\PrivacyBundle\ORM\Entity\Legacy;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

class LegacyCommandTest extends KernelTestCase
{
    /**
     * @var PrivacyManagerInterface|MockObject
     */
    private $privacyManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        parent::setUp();

        $this->privacyManager = $this->createMock(PrivacyManagerInterface::class);

        $application = new Application();
        $application->add(new LegacyCommand($this->privacyManager, ['default']));
        $command = $application->find('privacy:legacy');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct()
    {
        $command = new LegacyCommand($this->privacyManager, []);

        $this->assertInstanceOf(LegacyCommand::class, $command);
    }

    public function testConfigure()
    {
        /** @var LegacyCommandClazz|MockObject $command */
        $command = $this->getMockBuilder(LegacyCommandClazz::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setDescription', 'addOption'])
            ->getMock();

        $command->expects($this->once())->method('setName')
            ->with('privacy:legacy')
            ->willReturnSelf();
        $command->expects($this->once())->method('setDescription')
            ->willReturnSelf();
        $command->expects($this->exactly(2))->method('addOption')
            ->withConsecutive(
                ['limit', 'l', InputOption::VALUE_OPTIONAL],
                ['om', null, InputOption::VALUE_OPTIONAL]
            )
            ->willReturnSelf();

        $command->simulateConfigure();
    }

    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        $user = $this->createMock(ObjectReferenceInterface::class);
        $object = $this->createMock(ObjectReferenceInterface::class);
        $date = new \DateTime();

        $legacy = $this->createMock(Legacy::class);
        $legacy->expects($this->once())->method('getId')->willReturn(1);
        $legacy->expects($this->once())->method('getApplicationPolicy')->willReturn('QOne\\PrivacyBundle\\Policy\\NullPolicy');
        $legacy->expects($this->once())->method('getObject')->willReturn($object);
        $legacy->expects($this->once())->method('getUser')->willReturn($user);
        $legacy->expects($this->once())->method('getApplicationTimestamp')->willReturn($date);

        $result = $this->createMock(LegacyResult::class);
        $result->expects($this->exactly(5))->method('getLegacy')->willReturn($legacy);

        $this->privacyManager->expects($this->once())->method('doLegacy')->willReturn($result);

        $this->commandTester->execute([]);
    }
}

class LegacyCommandClazz extends LegacyCommand
{
    public function simulateConfigure()
    {
        return $this->configure();
    }
}
