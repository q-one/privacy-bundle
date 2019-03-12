<?php

namespace QOne\PrivacyBundle\Tests\Unit\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use QOne\PrivacyBundle\Condition\ConditionInterface;
use QOne\PrivacyBundle\Condition\PredictingEvaluatorInterface;
use QOne\PrivacyBundle\Exception\InvalidMetadataException;
use QOne\PrivacyBundle\Exception\LogicException;
use QOne\PrivacyBundle\Manager\PrivacyManager;
use QOne\PrivacyBundle\Mapping\ClassMetadataInterface;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Obsolescence\LegacyResult;
use QOne\PrivacyBundle\Obsolescence\ObsolescenceResult;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;
use QOne\PrivacyBundle\Persistence\PersistenceManagerInterface;
use QOne\PrivacyBundle\Persistence\Repository\AssetRepositoryInterface;
use QOne\PrivacyBundle\Persistence\Repository\LegacyRepositoryInterface;
use QOne\PrivacyBundle\Persistence\Repository\StateRepositoryInterface;
use QOne\PrivacyBundle\Policy\PolicyInterface;
use QOne\PrivacyBundle\Verdict\PrejudgingVerdict;
use QOne\PrivacyBundle\Verdict\UnanimousVerdict;
use QOne\PrivacyBundle\Verdict\VerdictInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrivacyManagerTest extends TestCase
{
    /**
     * @var MetadataRegistryInterface|MockObject
     */
    protected $metadataRegistry;

    /**
     * @var ManagerRegistry|MockObject
     */
    protected $registry;

    /**
     * @var PersistenceManagerInterface|MockObject
     */
    protected $persistenceManager;

    /**
     * @var LegacyRepositoryInterface|MockObject
     */
    protected $legacyRepository;

    /**
     * @var ObjectReferenceInterface|MockObject
     */
    protected $objectReference;

    /**
     * @var StateInterface|MockObject
     */
    protected $stateRepository;

    /**
     * @var ObjectManager|MockObject
     */
    protected $om;

    /**
     * @var ClassMetadataFactory|MockObject
     */
    protected $classMetadataFactory;

    /**
     * @var AssetRepositoryInterface|MockObject
     */
    protected $assetRepository;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var AssetInterface|MockObject
     */
    protected $assetInterface;

    /**
     * @var ContainerInterface|MockObject
     */
    protected $container;

    /**
     * @var VerdictInterface|MockObject
     */
    protected $verdict;

    /**
     * @var GroupMetadataInterface|MockObject
     */
    protected $groupMetadata;

    /**
     * @var ClassMetadataInterface|MockObject
     */
    protected $classMetadata;

    /**
     * @var PrejudgingVerdict
     */
    protected $prejudgingVerdict;

    /**
     * @var LegacyInterface|MockObject
     */
    protected $legacyInterface;

    /**
     * @var PolicyInterface|MockObject
     */
    protected $policy;

    /**
     * @var ConditionInterface|MockObject
     */
    protected $condition;

    /**
     * @var PredictingEvaluatorInterface|MockObject
     */
    protected $evaluator;

    /**
     * @var UnanimousVerdict
     */
    protected $unanimousVerdict;


    protected function setUp()
    {
        parent::setUp();
        $this->metadataRegistry = $this->createMock(MetadataRegistryInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->legacyRepository = $this->createMock(LegacyRepositoryInterface::class);
        $this->objectReference = $this->createMock(ObjectReferenceInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetInterface = $this->createMock(AssetInterface::class);
        $this->om = $this->createMock(ObjectManager::class);
        $this->classMetadataFactory = $this->createMock(ClassMetadataFactory::class);

        $this->legacyInterface = $this->createMock(LegacyInterface::class);
        $this->policy = $this->createMock(PolicyInterface::class);

        $this->verdict = $this->getMockBuilder(VerdictInterface::class)->setMethods(
            [
                'create',
                'getObject',
                'getClassMetadata',
                'getGroupMetadata',
                'addVote',
                'getObsoleteVotes',
                'getAppropriateVotes',
                'getKeepVotes',
                'resetVotes',
                'isObsolete'
            ]
        )->getMock();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->groupMetadata = $this->createMock(GroupMetadataInterface::class);
        $this->classMetadata = $this->createMock(ClassMetadataInterface::class);
        $this->prejudgingVerdict = new PrejudgingVerdict(new \stdClass(), $this->classMetadata, $this->groupMetadata);
        $this->unanimousVerdict = new UnanimousVerdict(new \stdClass(), $this->classMetadata, $this->groupMetadata);

        $this->condition = $this->createMock(ConditionInterface::class);
        $this->evaluator = $this->createMock(PredictingEvaluatorInterface::class);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(PrivacyManager::class, $this->getPrivacyManager());
    }

    public function testDoLegacy()
    {
        $this->logger->expects($this->once())->method('notice');
        $this->om->expects($this->once())->method('getClassMetadata')->willReturn(true);

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);
        $this->legacyRepository->expects($this->once())->method('findNextLegacy')->willReturn($this->legacyInterface);

        $this->om->expects($this->at(1))->method('getRepository')->with(LegacyInterface::class)
            ->willReturn($this->legacyRepository);
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)
            ->willReturn($this->stateRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(4))->method('getRepository')->willReturn($testClazz);

        $this->container->expects($this->once())->method('getParameter')->willReturn(PolicyInterface::class);
        $this->container->expects($this->once())->method('get')->willReturn($this->policy);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doLegacy('objectManagerName');
        $this->assertInstanceOf(LegacyResult::class, $result);
    }

    public function testDoLegacyEEOF()
    {
        $this->logger->expects($this->once())->method('debug');

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);
        $this->legacyRepository->expects($this->once())->method('findNextLegacy')->willReturn(null);

        $this->om->expects($this->at(1))->method('getRepository')->with(LegacyInterface::class)
            ->willReturn($this->legacyRepository);
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)
            ->willReturn($this->stateRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $result = $manager->doLegacy('objectManagerName');
        $this->assertInstanceOf(LegacyResult::class, $result);
    }

    public function testDoLegacyASEnoSuchClass()
    {
        $this->logger->expects($this->once())->method('info');
        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);
        $this->legacyRepository->expects($this->once())->method('findNextLegacy')->willReturn($this->legacyInterface);

        $this->om->expects($this->at(1))->method('getRepository')->with(LegacyInterface::class)
            ->willReturn($this->legacyRepository);
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)
            ->willReturn($this->stateRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $result = $manager->doLegacy('objectManagerName');
        $this->assertInstanceOf(LegacyResult::class, $result);
    }

    /**
     * @throws \Exception
     */
    public function testDoObsolescence()
    {
        $this->evaluator->expects($this->once())->method('predict')->willReturn(new \DateInterval('P3D'));
        $this->container->expects($this->at(1))->method('get')->willReturn($this->evaluator);
        $this->groupMetadata->expects($this->once())->method('getConditions')->willReturn([$this->condition]);
        $this->classMetadata->expects($this->once())->method('getGroup')->willReturn($this->groupMetadata);
        $this->metadataRegistry->expects($this->once())->method('getMetadataFor')->willReturn($this->classMetadata);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->legacyRepository->expects($this->never())->method('leaveLegacy')->willReturn($this->legacyInterface);
        $this->om->expects($this->at(4))->method('getRepository')->willReturn($this->legacyRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->exactly(3))->method('debug');

        $this->container->expects($this->at(0))->method('getParameter')->willReturn($this->unanimousVerdict);
        $this->container->expects($this->at(1))->method('getParameter')->willReturn(PolicyInterface::class);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithIsObsolete()
    {
        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->legacyRepository->expects($this->once())->method('leaveLegacy')->willReturn($this->legacyInterface);
        $this->om->expects($this->at(4))->method('getRepository')->willReturn($this->legacyRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->once())->method('debug');

        $this->container->expects($this->at(0))->method('getParameter')->willReturn($this->prejudgingVerdict);
        $this->container->expects($this->at(1))->method('getParameter')->willReturn(PolicyInterface::class);

        $this->container->expects($this->once())->method('get')->willReturn($this->policy);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithConditionAndInvalidMetadataException()
    {
        $this->expectException(InvalidMetadataException::class);
        $this->container->expects($this->at(1))->method('get')->willReturn($this->evaluator);
        $this->groupMetadata->expects($this->once())->method('getConditions')->willReturn([$this->condition]);
        $this->classMetadata->expects($this->once())->method('getGroup')->willReturn($this->groupMetadata);
        $this->metadataRegistry->expects($this->once())->method('getMetadataFor')->willReturn($this->classMetadata);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->legacyRepository->expects($this->once())->method('leaveLegacy')->willReturn($this->legacyInterface);
        $this->om->expects($this->at(4))->method('getRepository')->willReturn($this->legacyRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->exactly(2))->method('debug');

        $this->container->expects($this->at(0))->method('getParameter')->willReturn($this->prejudgingVerdict);
        $this->container->expects($this->at(1))->method('getParameter')->willReturn(PolicyInterface::class);

        $this->container->expects($this->at(2))->method('get')->willReturn($this->policy);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithConditionAndLogicException()
    {
        $this->expectException(LogicException::class);
        $this->groupMetadata->expects($this->once())->method('getConditions')->willReturn([$this->condition]);
        $this->classMetadata->expects($this->once())->method('getGroup')->willReturn($this->groupMetadata);
        $this->metadataRegistry->expects($this->once())->method('getMetadataFor')->willReturn($this->classMetadata);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->never())->method('debug');

        $this->container->expects($this->at(0))->method('getParameter')->willReturn($this->prejudgingVerdict);
        $this->container->expects($this->at(1))->method('getParameter')->willReturn(PolicyInterface::class);

        $this->container->expects($this->once())->method('get')->willReturn($this->policy);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithInvalidMetadataException()
    {
        $this->expectException(InvalidMetadataException::class);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->exactly(2))->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->legacyRepository->expects($this->once())->method('leaveLegacy')->willReturn($this->legacyInterface);
        $this->om->expects($this->at(4))->method('getRepository')->willReturn($this->legacyRepository);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->once())->method('debug');

        $this->container->expects($this->at(0))->method('getParameter')->willReturn($this->prejudgingVerdict);
        $this->container->expects($this->at(1))->method('getParameter')->willReturn(null);

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithoutVerdictInterface()
    {
        $this->expectException(InvalidMetadataException::class);
        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(new \stdClass());
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->never())->method('debug');


        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $manager->setContainer($this->container);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceNoObject()
    {
        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->assetRepository->expects($this->once())->method('findNextValidationAsset')->willReturn(
            $this->assetInterface
        );

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $testClazz = $this->createMock(MyNewTestClazz::class);
        $testClazz->expects($this->once())->method('find')->willReturn(null);
        $this->om->expects($this->at(3))->method('getRepository')->willReturn($testClazz);

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->never())->method('debug');

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceNoAsset()
    {
        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(true);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);
        $this->stateRepository->expects($this->once())->method('getIntValue')->willReturn(1);

        $this->om->expects($this->at(1))->method('getRepository')->with(AssetInterface::class)->willReturn(
            $this->assetRepository
        );
        $this->om->expects($this->at(2))->method('getRepository')->with(StateInterface::class)->willReturn(
            $this->stateRepository
        );

        $this->persistenceManager->expects($this->once())->method('transactional')->willReturnCallback(
            function ($objectManager, $callback) {
                return $callback($objectManager);
            }
        );

        $this->logger->expects($this->once())->method('debug');

        $manager = $this->getPrivacyManager();
        $manager->setLogger($this->logger);
        $result = $manager->doObsolescence('objectManagerName');

        $this->assertInstanceOf(ObsolescenceResult::class, $result);
    }

    public function testDoObsolescenceWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(false);
        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);

        $this->registry->expects($this->once())->method('getManager')->willReturn($this->om);

        $manager = $this->getPrivacyManager();
        $manager->doObsolescence('objectManagerName');
    }

    /**
     * @throws \Exception
     */
    public function testGetObjectManager()
    {
        $manager = $this->getPrivacyManager();
        $this->assertNull($manager->setReevaluationInterval('P1D'));
    }

    /**
     * @throws \Exception
     */
    public function testGetAssetsForUser()
    {
        $this->classMetadataFactory->expects($this->once())->method('isTransient')->willReturn(false);

        $this->assetRepository->expects($this->once())->method('findAssetsForUser')->willReturn([]);

        $this->om->expects($this->once())->method('getMetadataFactory')->willReturn($this->classMetadataFactory);
        $this->om->expects($this->once())->method('getRepository')->willReturn($this->assetRepository);

        $this->registry->expects($this->once())->method('getManagers')->willReturn([$this->om]);

        $manager = $this->getPrivacyManager();
        $this->assertInstanceOf(ArrayCollection::class, $manager->getAssetsForUser($this->objectReference));
    }

    /**
     * @throws \Exception
     */
    public function testGetObjectManagerWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $manager = $this->getPrivacyManager();
        $manager->setReevaluationInterval(true);
    }

    public function getPrivacyManager()
    {
        return new PrivacyManager($this->metadataRegistry, $this->registry, $this->persistenceManager, 'foo');
    }
}

class MyNewTestClazz
{
    public function find()
    {

    }
}
