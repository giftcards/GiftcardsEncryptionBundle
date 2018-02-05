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

class AddCipherTextStoresPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('giftcards.encryption.cipher_text_store.registry')) {
            return;
        }
        
        $registry = $container->getDefinition('giftcards.encryption.cipher_text_store.registry');

        foreach ($container->findTaggedServiceIds('giftcards.encryption.cipher_text_store') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'The service "%s" tagged giftcards.encryption.cipher_text_store must have an "alias" key given.',
                        $id
                    ));
                }
                $registry->addMethodCall('setServiceId', array($tag['alias'], $id));
            }
        }
    }
}