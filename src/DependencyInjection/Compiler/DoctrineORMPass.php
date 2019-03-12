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

namespace QOne\PrivacyBundle\DependencyInjection\Compiler;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use QOne\PrivacyBundle\ORM\Entity\Asset;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\ORM\Entity\Legacy;
use QOne\PrivacyBundle\ORM\Entity\ObservedEntityReference;
use QOne\PrivacyBundle\ORM\Entity\State;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\ObservedObjectReferenceInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineORMPass implements CompilerPassInterface
{
    const OBJECT_ASSET_MAP = [
        AssetInterface::class => Asset::class,
        StateInterface::class => State::class,
        ObjectReferenceInterface::class => EntityReference::class,
        ObservedObjectReferenceInterface::class => ObservedEntityReference::class,
    ];

    const OBJECT_LEGACY_MAP = [
        LegacyInterface::class => Legacy::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processMappingDriver($container);
        $this->processResolveTargetEntityListener($container);

        $assetEntityManagers = $container->getParameter('qone_privacy.persistence.object_managers.asset');
        $legacyEntityManager = $container->getParameter('qone_privacy.persistence.object_managers.legacy');

        foreach ($assetEntityManagers as $em) {
            foreach (self::OBJECT_ASSET_MAP as $objectInterface => $entityClass) {
                $this->processMapping($container, $em, $entityClass);
            }
        }

        foreach (self::OBJECT_LEGACY_MAP as $objectInterface => $entityClass) {
            $this->processMapping($container, $legacyEntityManager, $entityClass);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processMappingDriver(ContainerBuilder $container): void
    {
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        $ormEntityDir = sprintf('%s/ORM/Entity/', $bundlesMetadata['QOnePrivacyBundle']['path']);
        $reader = new Reference('annotation_reader');
        $mappingDriverDef = new Definition(AnnotationDriver::class, [$reader, [$ormEntityDir]]);

        $container->setDefinition('qone_privacy.doctrine.orm.mapping_driver', $mappingDriverDef);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processResolveTargetEntityListener(ContainerBuilder $container): void
    {
        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        $resolveTargetEntityListener->addTag('doctrine.event_subscriber');

        foreach (static::OBJECT_ASSET_MAP + static::OBJECT_LEGACY_MAP as $objectInterface => $entityClass) {
            $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [
                $objectInterface,
                $entityClass,
                [],
            ]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $managerName
     * @param string           $className
     */
    private function processMapping(ContainerBuilder $container, string $managerName, string $className)
    {
        try {
            $container->findDefinition(sprintf('doctrine.orm.%s_metadata_driver', $managerName))
                ->addMethodCall(
                    'addDriver',
                    [
                        new Reference('qone_privacy.doctrine.orm.mapping_driver'),
                        $className,
                    ]
                );
        } catch (ServiceNotFoundException $e) {
            throw new InvalidConfigurationException(sprintf(
                'Invalid entity manager defined for QOnePrivacyBundle\'s %s: %s',
                $className, $managerName
            ), 0, $e);
        }
    }
}
