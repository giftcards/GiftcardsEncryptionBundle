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

class AddEncryptorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omni.encryption.encryptor.registry')) {
            return;
        }
        
        $registry = $container->getDefinition('omni.encryption.encryptor.registry');

        foreach ($container->findTaggedServiceIds('omni.encryption.encryptor') as $id => $tags) {
            $registry->addMethodCall('add', array(new Reference($id)));
        }
    }
}