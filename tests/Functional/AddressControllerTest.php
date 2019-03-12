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

namespace QOne\PrivacyBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;
use QOne\PrivacyBundle\Persistence\Repository\AssetRepositoryInterface;
use QOne\PrivacyBundle\Persistence\Repository\StateRepositoryInterface;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\DataFixtures\AddressFixtures;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\DataFixtures\LegacyFixtures;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\DataFixtures\StateFixtures;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\DataFixtures\UserFixtures;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\Address;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class AddressControllerTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $kernelContainer;

    /**
     * @var AssetRepositoryInterface
     */
    protected $assetRepository;

    /**
     * @var StateRepositoryInterface
     */
    protected $stateRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->client = $this->createClient(['test_case' => 'AddressControllerTest']);
        $this->containers[$this->environment] = $this->client->getContainer(); // TODO monkey!

        $this->kernelContainer = self::$kernel->getContainer();

        $this->entityManager = $this->kernelContainer->get('doctrine.orm.default_entity_manager');
        $this->stateRepository = $this->entityManager->getRepository(StateInterface::class);
        $this->assetRepository = $this->entityManager->getRepository(AssetInterface::class);
    }

    public function testCreateAddress()
    {
        $this->client->request('POST', '/address');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @throws \Exception
     */
    public function testObsolescenceCommand()
    {
        $this->loadFixtures([
            UserFixtures::class,
            AddressFixtures::class,
            StateFixtures::class,
        ]);

        $this->assertNotEquals(1, $this->stateRepository->getIntValue(StateInterface::KEY_LEGACY_POSITION));

        /* @var Address $address */
        $address = $this->entityManager->getRepository(Address::class)->find(1);

        $this->assertNotNull($address->getStreet());
        $this->assertNotNull($address->getZip());
        $this->assertNotNull($address->getCity());

        $exitCode = $this->executeCommand('privacy:obsolescence', ['--om' => 'default']);
        $this->assertEquals($exitCode, 0);

        /* @var Address $address */
        $address = $this->entityManager->getRepository(Address::class)->find(1);
        $this->assertNull($address->getStreet());
        $this->assertNull($address->getZip());
        $this->assertNull($address->getCity());

        $this->assertEquals(1, $this->stateRepository->getIntValue(StateInterface::KEY_LEGACY_POSITION));
    }

    /**
     * @throws \Exception
     */
    public function testLegacyCommand()
    {
        $this->loadFixtures([
            UserFixtures::class,
            AddressFixtures::class,
            StateFixtures::class,
            LegacyFixtures::class,
        ]);

        $this->assertNotEquals(1, $this->stateRepository->getIntValue(StateInterface::KEY_LEGACY_POSITION));

        /* @var Address $address */
        $address = $this->entityManager->getRepository(Address::class)->find(1);

        $this->assertNotNull($address->getStreet());
        $this->assertNotNull($address->getZip());
        $this->assertNotNull($address->getCity());

        $exitCode = $this->executeCommand('privacy:legacy');

        /** @var Address $address */
        $address = $this->entityManager->getRepository(Address::class)->find(1);

        $this->assertEquals($exitCode, 0);
        $this->assertNull($address->getStreet());
        $this->assertNull($address->getZip());
        $this->assertNull($address->getCity());
    }

    /**
     * @throws \Exception
     */
    public function testCacheWarmup()
    {
        $exitCode = $this->executeCommand('cache:warmup');
        $this->assertEquals($exitCode, 0);
    }

    /**
     * @param string $command
     * @param array  $inputOptions
     *
     * @return int
     *
     * @throws \Exception
     */
    private function executeCommand(string $command, $inputOptions = []): int
    {
        $application = new Application(self::$kernel);

        $input = new ArrayInput($inputOptions);
        $input->setInteractive(false);

        $output = new ConsoleOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $command = $application->find($command);

        return $command->run($input, $output);
    }
}
