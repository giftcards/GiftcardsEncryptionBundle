<?php

namespace Omni\EncryptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OmniEncryptionExtension extends Extension
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

        $profileRegistry = $container->getDefinition('omni.encryption.profile.registry');
        
        foreach ($config['profiles'] as $profile => $profileConfig) {
            $profileRegistry->addMethodCall('set', array($profile, new Definition(
                'Omni\Encryption\Profile\Profile',
                array($profileConfig['cipher'], $profileConfig['key_name'])
            )));
        }
        
        $container
            ->getDefinition('omni.encryption.encryptor')
            ->replaceArgument(4, $config['default_profile'])
        ;
        
        if (count($config['keys']['fallbacks'])) {
            $container->register('omni.encryption.key_source.fallback', 'Omni\Encryption\Key\FallbackSource')
                ->setArguments(array(
                    $config['keys']['fallbacks'],
                    new Reference((string)$container->getAlias('omni.encryption.key_source'))
                ))
            ;
            $container->setAlias('omni.encryption.key_source', 'omni.encryption.key_source.fallback');
        }
        
        if (count($config['keys']['map'])) {
            $container->register('omni.encryption.key_source.mapping', 'Omni\Encryption\Key\MappingSource')
                ->setArguments(array(
                    $config['keys']['map'],
                    new Reference((string)$container->getAlias('omni.encryption.key_source'))
                ))
            ;
            $container->setAlias('omni.encryption.key_source', 'omni.encryption.key_source.mapping');
        }
        
        if ($config['key_sources']['cache']) {
            $container->register('omni.encryption.key_source.caching', 'Omni\Encryption\Key\CachingSource')
                ->setArguments(array(
                    $config['keys']['map'],
                    new Reference((string)$container->getAlias('omni.encryption.key_source')),
                    new Definition('Doctrine\Common\Cache\ArrayCache')
                ))
            ;
            $container->setAlias('omni.encryption.key_source', 'omni.encryption.key_source.caching');
        }
    }
}
