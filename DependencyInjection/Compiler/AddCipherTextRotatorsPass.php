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

class AddCipherTextRotatorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omni.encryption.cipher_text_rotator.registry')) {
            return;
        }
        
        $registry = $container->getDefinition('omni.encryption.cipher_text_rotator.registry');

        foreach ($container->findTaggedServiceIds('omni.encryption.cipher_text_rotator') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'The service "%s" tagged omni.encryption.cipher_text_rotator must have an "alias" key given.',
                        $id
                    ));
                }
                $registry->addMethodCall('setServiceId', array($tag['alias'], $id));
            }
        }
    }
}