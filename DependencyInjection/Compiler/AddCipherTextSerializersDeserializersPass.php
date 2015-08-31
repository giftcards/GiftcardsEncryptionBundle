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

class AddCipherTextSerializersDeserializersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('omni.encryption.cipher_text_serializer.chain')) {
            $chain = $container->getDefinition('omni.encryption.cipher_text_serializer.chain');

            foreach ($container->findTaggedServiceIds('omni.encryption.cipher_text_serializer') as $id => $tags) {
                foreach ($tags as $tag) {
                    $chain->addMethodCall('addServiceId', array($id, isset($tag['priority']) ? $tag['priority'] : 0));
                }
            }
        }

        if ($container->hasDefinition('omni.encryption.cipher_text_deserializer.chain')) {
            $chain = $container->getDefinition('omni.encryption.cipher_text_deserializer.chain');

            foreach ($container->findTaggedServiceIds('omni.encryption.cipher_text_deserializer') as $id => $tags) {
                foreach ($tags as $tag) {
                    $chain->addMethodCall('addServiceId', array($id, isset($tag['priority']) ? $tag['priority'] : 0));
                }
            }
        }

    }
}