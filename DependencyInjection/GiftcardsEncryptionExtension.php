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
        $loader->load('builders.yml');

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
            $container->register('giftcards.encryption.key_source.fallback.circular_guard', 'Giftcards\Encryption\Key\CircularGuardSource')
                ->setArguments(array(
                    new Reference('giftcards.encryption.key_source.fallback')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.fallback.circular_guard'))
            ;
        }
        
        if (count($config['keys']['map'])) {
            $container->register('giftcards.encryption.key_source.mapping', 'Giftcards\Encryption\Key\MappingSource')
                ->setArguments(array(
                    $config['keys']['map'],
                    new Reference('giftcards.encryption.key_source')
                ))
            ;
            $container->register('giftcards.encryption.key_source.mapping.circular_guard', 'Giftcards\Encryption\Key\CircularGuardSource')
                ->setArguments(array(
                    new Reference('giftcards.encryption.key_source.mapping')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.mapping.circular_guard'))
            ;
        }
        
        if (count($config['keys']['combine'])) {
            $container->register('giftcards.encryption.key_source.combining', 'Giftcards\Encryption\Key\CombiningSource')
                ->setArguments(array(
                    $config['keys']['combine'],
                    new Reference('giftcards.encryption.key_source')
                ))
            ;
            $container->register('giftcards.encryption.key_source.combining.circular_guard', 'Giftcards\Encryption\Key\CircularGuardSource')
                ->setArguments(array(
                    new Reference('giftcards.encryption.key_source.combining')
                ))
            ;
            $container->getDefinition('giftcards.encryption.key_source.chain')
                ->addMethodCall('addServiceId', array('giftcards.encryption.key_source.combining.circular_guard'))
            ;
        }
        
        if ($config['keys']['cache']) {
            $container->register('giftcards.encryption.key_source.caching', 'Giftcards\Encryption\Key\CachingSource')
                ->setArguments(array(
                    new Definition('Doctrine\Common\Cache\ArrayCache'),
                    new Reference((string)$container->getAlias('giftcards.encryption.key_source'))
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
                    array(
                        'prefix' => $sourceConfig['prefix'],
                        'add_circular_guard' => $sourceConfig['add_circular_guard']
                    )
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

        foreach ($config['cipher_texts']['stores'] as $name => $rotatorConfig) {
            $serviceId = sprintf('giftcards.encryption.cipher_text_store.%s', $name);
            $container
                ->setDefinition(
                    $serviceId,
                    new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_store')
                )
                ->replaceArgument(0, $rotatorConfig['type'])
                ->replaceArgument(1, $rotatorConfig['options'])
                ->addTag(
                    'giftcards.encryption.cipher_text_store',
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

        if ($config['doctrine']['encrypted_properties']['enabled']) {
            
            $loader->load('doctrine_encrypted_properties.yml');

            foreach ($config['doctrine']['encrypted_properties']['orm']['connections'] as $connection) {
                $listener = new DefinitionDecorator('giftcards.encryption.listener.abstract_encrypted_listener');
                $listener->addTag(
                    'doctrine.event_subscriber',
                    array('connection' => $connection)
                );
                $container->setDefinition(
                    sprintf('giftcards.encryption.listener.encrypted_listener.orm.%s', $connection),
                    $listener
                );
            }
            
            foreach ($config['doctrine']['encrypted_properties']['odm']['connections'] as $connection) {
                $listener = new DefinitionDecorator('giftcards.encryption.listener.abstract_encrypted_listener');
                $listener->addTag(
                    'doctrine_mongodb.odm.event_subscriber',
                    array('connection' => $connection)
                );
                $container->setDefinition(
                    sprintf('giftcards.encryption.listener.encrypted_listener.odm.%s', $connection),
                    $listener
                );
            }
        }
    }
}
