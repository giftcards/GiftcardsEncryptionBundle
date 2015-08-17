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

class AddCiphersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omni.encryption.cipher.registry')) {
            return;
        }
        
        $registry = $container->getDefinition('omni.encryption.cipher.registry');

        foreach ($container->findTaggedServiceIds('omni.encryption.cipher') as $id => $tags) {
            $registry->addMethodCall('add', array(new Reference($id)));
        }
    }
}