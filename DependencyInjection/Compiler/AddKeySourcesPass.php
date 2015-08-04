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

class AddKeySourcesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omni.encryption.key_source.chain')) {
            return;
        }
        
        $chain = $container->getDefinition('omni.encryption.key_source.chain');

        foreach ($container->findTaggedServiceIds('omni.encryption.key_source') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'The service "%s" tagged omni.encryption.key_source must have an alias key given.',
                        $id
                    ));
                }
                $chain->addMethodCall('set', array($tag['alias'], new Reference($id)));
            }
        }
    }
}