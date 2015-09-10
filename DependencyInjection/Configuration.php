<?php

namespace Giftcards\EncryptionBundle\DependencyInjection;

use Giftcards\Encryption\Key\CombiningSource;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('giftcards_encryption');

        $rootNode
            ->children()
                ->arrayNode('cipher_texts')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('rotators')
                            ->defaultValue(array())
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')
                                        ->defaultValue(array())
                                        ->prototype('variable')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('serializers')
                            ->defaultValue(array())
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')
                                        ->defaultValue(array())
                                        ->prototype('variable')
                                        ->end()
                                    ->end()
                                    ->integerNode('priority')->defaultValue(0)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('deserializers')
                            ->defaultValue(array())
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')
                                        ->defaultValue(array())
                                        ->prototype('variable')
                                        ->end()
                                    ->end()
                                    ->integerNode('priority')->defaultValue(0)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('profiles')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('cipher')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('key_name')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('keys')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('sources')
                            ->defaultValue(array())
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')
                                        ->defaultValue(array())
                                        ->prototype('variable')
                                        ->end()
                                    ->end()
                                    ->scalarNode('prefix')->defaultValue('')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->booleanNode('cache')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('map')
                            ->defaultValue(array())
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->cannotBeEmpty()->end()
                        ->end()
                        ->arrayNode('fallbacks')
                            ->defaultValue(array())
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->prototype('scalar')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('combine')
                            ->defaultValue(array())
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode(CombiningSource::LEFT)->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode(CombiningSource::RIGHT)->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('profiles')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('cipher')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('key_name')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_profile')->defaultNull()->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
