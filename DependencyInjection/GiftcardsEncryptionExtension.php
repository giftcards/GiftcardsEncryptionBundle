<?php

namespace Giftcards\EncryptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GiftcardsEncryptionExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('factories.yml');

        $profileRegistry = $container->getDefinition('giftcards.encryption.profile.registry');
        
        foreach ($config['profiles'] as $profile => $profileConfig) {
            $profileRegistry->addMethodCall('set', array($profile, new Definition(
                'Giftcards\Encryption\Profile\Profile',
                array($profileConfig['cipher'], $profileConfig['key_name'])
            )));
        }
        
        $container
            ->getDefinition('giftcards.encryption.encryptor')
            ->replaceArgument(4, $config['default_profile'])
        ;
        
        if (count($config['keys']['fallbacks'])) {
            $container->register('giftcards.encryption.key_source.fallback', 'Giftcards\Encryption\Key\FallbackSource')
                ->setArguments(array(
                    $config['keys']['fallbacks'],
                    new Reference('giftcards.encryption.key_source')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.fallback'))
            ;
        }
        
        if (count($config['keys']['map'])) {
            $container->register('giftcards.encryption.key_source.mapping', 'Giftcards\Encryption\Key\MappingSource')
                ->setArguments(array(
                    $config['keys']['map'],
                    new Reference('giftcards.encryption.key_source')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.mapping'))
            ;
        }
        
        if (count($config['keys']['combine'])) {
            $container->register('giftcards.encryption.key_source.combining', 'Giftcards\Encryption\Key\CombiningSource')
                ->setArguments(array(
                    $config['keys']['combine'],
                    new Reference('giftcards.encryption.key_source')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.combining'))
            ;
        }
        
        if ($config['keys']['cache']) {
            $container->register('giftcards.encryption.key_source.caching', 'Giftcards\Encryption\Key\CachingSource')
                ->setArguments(array(
                    new Reference((string)$container->getAlias('giftcards.encryption.key_source')),
                    new Definition('Doctrine\Common\Cache\ArrayCache')
                ))
            ;
            $container->setAlias('giftcards.encryption.key_source', 'giftcards.encryption.key_source.caching');
        }

        foreach ($config['keys']['sources'] as $name => $sourceConfig) {
            $serviceId = sprintf('giftcards.encryption.key_source.%s', $name);
            $container
                ->setDefinition(
                    $serviceId,
                    new DefinitionDecorator('giftcards.encryption.abstract_key_source')
                )
                ->replaceArgument(0, $sourceConfig['type'])
                ->replaceArgument(1, $sourceConfig['options'])
                ->addTag(
                    'giftcards.encryption.key_source',
                    array('prefix' => $sourceConfig['prefix'])
                )
            ;
        }

        foreach ($config['cipher_texts']['rotators'] as $name => $rotatorConfig) {
            $serviceId = sprintf('giftcards.encryption.cipher_text_rotator.%s', $name);
            $container
                ->setDefinition(
                    $serviceId,
                    new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_rotator')
                )
                ->replaceArgument(0, $rotatorConfig['type'])
                ->replaceArgument(1, $rotatorConfig['options'])
                ->addTag(
                    'giftcards.encryption.cipher_text_rotator',
                    array('alias' => $name)
                )
            ;
        }

        foreach ($config['cipher_texts']['serializers'] as $name => $serializerConfig) {
            $serviceId = sprintf('giftcards.encryption.cipher_text_serializer.%s', $name);
            $container
                ->setDefinition(
                    $serviceId,
                    new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_serializer')
                )
                ->replaceArgument(0, $serializerConfig['type'])
                ->replaceArgument(1, $serializerConfig['options'])
                ->addTag(
                    'giftcards.encryption.cipher_text_serializer',
                    array('priority' => $serializerConfig['priority'])
                )
            ;
        }

        foreach ($config['cipher_texts']['deserializers'] as $name => $deserializerConfig) {
            $serviceId = sprintf('giftcards.encryption.cipher_text_deserializer.%s', $name);
            $container
                ->setDefinition(
                    $serviceId,
                    new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_deserializer')
                )
                ->replaceArgument(0, $deserializerConfig['type'])
                ->replaceArgument(1, $deserializerConfig['options'])
                ->addTag(
                    'giftcards.encryption.cipher_text_deserializer',
                    array('priority' => $deserializerConfig['priority'])
                )
            ;
        }
    }
}
