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

class AddBuildersPass implements CompilerPassInterface
{
    protected $registries = array(
        'giftcards.encryption.key_source.factory.registry' => 'giftcards.encryption.key_source.builder',
        'giftcards.encryption.cipher_text_rotator.factory.registry' => 'giftcards.encryption.cipher_text_rotator.builder',
        'giftcards.encryption.cipher_text_store.factory.registry' => 'giftcards.encryption.cipher_text_store.builder',
        'giftcards.encryption.cipher_text_serializer.factory.registry' => 'giftcards.encryption.cipher_text_serializer.builder',
        'giftcards.encryption.cipher_text_deserializer.factory.registry' => 'giftcards.encryption.cipher_text_deserializer.builder',
    );
    
    public function process(ContainerBuilder $container)
    {
        foreach ($this->registries as $name => $tagName) {
            if (!$container->hasDefinition($name)) {
                continue;
            }
            
            $registry = $container->getDefinition($name);

            foreach ($container->findTaggedServiceIds($tagName) as $id => $tags) {
                $tag = $tags[0];

                if (!isset($tag['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'The service "%s" tagged %s must have an "alias" key given.',
                        $tagName,
                        $id
                    ));
                }
                
                $registry->addMethodCall('setServiceId', array($tag['alias'], $id));
            }
        }
    }
}