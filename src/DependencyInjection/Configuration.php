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

use QOne\PrivacyBundle\Controller\PublisherController;
use QOne\PrivacyBundle\Delivery\DeliveryFactoryInterface;
use QOne\PrivacyBundle\Policy\NullPolicy;
use QOne\PrivacyBundle\Survey\Format\JsonStrategy;
use QOne\PrivacyBundle\Transformer\IdentityFieldTransformer;
use QOne\PrivacyBundle\Verdict\UnanimousVerdict;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    const VALID_MAPPERS = ['orm', 'custom'];

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('qone_privacy', 'array');

        $rootNode
            ->children()
                ->arrayNode('collector')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->arrayNode('data_sources')
                            ->useAttributeAsKey('name')
                            ->fixXmlConfig('data-source')
                            ->arrayPrototype()
                                ->variablePrototype()->end()
                                ->validate()
                                    ->ifTrue(function ($def) { return empty($def['factory']) || !is_string($def['factory']); })
                                    ->thenInvalid('You must supply the factory attribute for a data source.')
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($def) { return !is_a($def['factory'], DeliveryFactoryInterface::class, true); })
                                    ->thenInvalid('The factory attribute must specify the fully-qualified class name of a DeliveryFactoryInterface.')
                                ->end()
                                ->validate()
                                    ->always(function ($def) {
                                        /** @var DeliveryFactoryInterface $factory */
                                        $factory = $def['factory'];
                                        $configuration = $def;

                                        unset($configuration['factory']); // do not supply the factory to the validation method
                                        $factory::validateDelivery($configuration);

                                        return $def;
                                    })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mapping')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('cache')->defaultValue('cache.system')->end()
                        ->booleanNode('lazy_loading')->defaultFalse()->end()
                        ->arrayNode('loaders')
                            ->useAttributeAsKey('name')
                            ->fixXmlConfig('loader')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('class')->isRequired()->end()
                                    ->arrayNode('paths')
                                        ->fixXmlConfig('path')
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                        ->scalarPrototype()->cannotBeEmpty()->end()
                                    ->end()
                                    ->arrayNode('excludes')
                                        ->fixXmlConfig('exclude')
                                        ->scalarPrototype()->cannotBeEmpty()->end()
                                    ->end()
                                    ->arrayNode('extensions')
                                        ->fixXmlConfig('extension')
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                        ->scalarPrototype()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('obsolescence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_policy')->defaultValue(NullPolicy::class)->end()
                        ->scalarNode('default_verdict')->defaultValue(UnanimousVerdict::class)->end()
                        ->scalarNode('reevaluation_interval')->defaultValue('PT12H')->end()
                    ->end()
                ->end()
                ->arrayNode('persistence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('mapper')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('custom')
                            ->validate()
                                ->ifNotInArray(self::VALID_MAPPERS)
                                ->thenInvalid(sprintf('Configured mapper should be one of "%s"', implode('", "', self::VALID_MAPPERS)))
                            ->end()
                        ->end()
                        ->arrayNode('object_managers')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('asset')
                                    ->defaultValue(['default'])
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function ($v) { return [$v]; })
                                    ->end()
                                    ->isRequired()
                                    ->requiresAtLeastOneElement()
                                    ->scalarPrototype()->cannotBeEmpty()->end()
                                ->end()
                                ->scalarNode('legacy')->defaultValue('default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('publisher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('controller')->defaultValue(PublisherController::class)->end()
                        ->scalarNode('default_audit_transformer')->defaultValue(IdentityFieldTransformer::class)->end()
                        ->scalarNode('encoding_strategy')->defaultValue(JsonStrategy::class)->end()
                        ->scalarNode('path')->defaultValue('/_privacy')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
