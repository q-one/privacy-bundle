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

namespace QOne\PrivacyBundle\DependencyInjection;

use QOne\PrivacyBundle\EventListener\PublisherListener;
use QOne\PrivacyBundle\Mapping\LazyLoadingMetadataRegistry;
use QOne\PrivacyBundle\Mapping\Loader\LoaderInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

/**
 * Class QOnePrivacyExtension.
 */
class QOnePrivacyExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     *
     * @codeCoverageIgnore
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $container->setParameter('qone_privacy.collector.data_sources', $config['collector']['data_sources']);
        $container->setParameter('qone_privacy.metadata.cache', $config['mapping']['cache']);
        $container->setParameter('qone_privacy.obsolescence.default_policy', $config['obsolescence']['default_policy']);
        $container->setParameter('qone_privacy.obsolescence.default_verdict', $config['obsolescence']['default_verdict']);
        $container->setParameter('qone_privacy.obsolescence.reevaluation_interval', $config['obsolescence']['reevaluation_interval']);
        $container->setParameter('qone_privacy.persistence.object_managers.asset', $config['persistence']['object_managers']['asset']);
        $container->setParameter('qone_privacy.persistence.object_managers.legacy', $config['persistence']['object_managers']['legacy']);

        $serviceFiles = [
            'cache', 'command', 'condition', 'controller',
            'delivery', 'manager', 'mapping', // 'persistence',
            'policy', 'survey_format', 'transformer',
        ];

        foreach ($serviceFiles as $file) {
            $loader->load(sprintf('%s.yaml', $file));
        }

        if ('custom' !== $mapper = $config['persistence']['mapper']) {
            $container->setParameter('qone_privacy.persistence.mapper', $mapper);
            $loader->load(sprintf('persistence-%s.yaml', $mapper));
        }

        $cache = new Reference($container->getParameter('qone_privacy.metadata.cache'));
        $cacheDefinition = new Definition(TagAwareAdapter::class, [$cache]);
        $container->setDefinition('qone_privacy.cache.metadata', $cacheDefinition);

        if ($config['publisher']['enabled']) {
            $defPublisherListener = new Definition(PublisherListener::class, [
                $config['publisher']['path'],
                $config['publisher']['controller'],
            ]);
            $defPublisherListener->addTag('kernel.event_subscriber');
            $container->setDefinition('qone_privacy.event_listener.publisher_listener', $defPublisherListener);
        }

        $loaders = [];

        foreach ($config['mapping']['loaders'] as $loaderAlias => $loaderConfig) {
            if (!class_exists($loaderConfig['class']) || !is_a($loaderConfig['class'], LoaderInterface::class, true)) {
                throw new InvalidConfigurationException(sprintf('Expected %s to be an instance of %s', $loaderConfig['class'], LoaderInterface::class));
            }

            $loaderId = sprintf('qone_privacy.mapping.loader.%s', $loaderAlias);

            $extensions = array_map(function ($e) {
                return sprintf('/%s$/', preg_quote($e, '/'));
            }, $loaderConfig['extensions']);

            $finderDef = new Definition(Finder::class);
            $finderDef->addMethodCall('name', $extensions);
            $finderDef->addMethodCall('in', $loaderConfig['paths']);

            if ($loaderConfig['excludes']) {
                $finderDef->addMethodCall('exclude', $loaderConfig['excludes']);
            }

            $loaderDef = new Definition($loaderConfig['class']);
            $loaderDef->setAutowired(true);
            $loaderDef->addMethodCall('setFinder', [
                $finderDef,
            ]);

            $container->setDefinition($loaderId, $loaderDef);
            $loaders[] = new Reference($loaderId);
        }

        $container->getDefinition('qone_privacy.mapping.loader')
            ->addMethodCall('addLoaders', [$loaders]);

        if ($config['mapping']['lazy_loading']) {
            $container->getDefinition('qone_privacy.mapping.metadata_registry')
                ->setClass(LazyLoadingMetadataRegistry::class);
        }

        $defaultAuditTransformer = new Alias($config['publisher']['default_audit_transformer']);
        $defaultAuditTransformer->setPublic(true);
        $container->setAlias('qone_privacy.publisher.default_audit_transformer', $defaultAuditTransformer);

        $encodingStrategy = new Alias($config['publisher']['encoding_strategy']);
        $encodingStrategy->setPublic(true);
        $container->setAlias('qone_privacy.publisher.encoding_strategy', $encodingStrategy);

        $container->getDefinition('qone_privacy.publisher')
            ->addMethodCall('setDefaultAuditTransformer', [
                new Reference('qone_privacy.publisher.default_audit_transformer'),
            ])
            ->addMethodCall('setEncodingStrategy', [
                new Reference('qone_privacy.publisher.encoding_strategy'),
            ]);
    }

    public function getAlias()
    {
        return 'qone_privacy';
    }
}
