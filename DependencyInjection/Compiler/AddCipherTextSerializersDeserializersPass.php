<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/3/15
 * Time: 7:58 PM
 */

namespace Giftcards\EncryptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddCipherTextSerializersDeserializersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')) {
            return;
        }
        
        $chain = $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain');

        foreach ($container->findTaggedServiceIds('giftcards.encryption.cipher_text_serializer') as $id => $tags) {
            foreach ($tags as $tag) {
                $chain->addMethodCall('addSerializerServiceId', array($id, isset($tag['priority']) ? $tag['priority'] : 0));
            }
        }

        foreach ($container->findTaggedServiceIds('giftcards.encryption.cipher_text_deserializer') as $id => $tags) {
            foreach ($tags as $tag) {
                $chain->addMethodCall('addDeserializerServiceId', array($id, isset($tag['priority']) ? $tag['priority'] : 0));
            }
        }
    }
}