<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/3/15
 * Time: 7:58 PM
 */

namespace Omni\EncryptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddEncryptedDataStoresPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omni.encryption.encrypted_data_store.registry')) {
            return;
        }
        
        $registry = $container->getDefinition('omni.encryption.encrypted_data_store.registry');

        foreach ($container->findTaggedServiceIds('omni.encryption.encrypted_data_store') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'The service "%s" tagged omni.encryption.encrypted_data_store must have an alias key given.',
                        $id
                    ));
                }
                $registry->addMethodCall('set', array($tag['alias'], new Reference($id)));
            }
        }
    }
}