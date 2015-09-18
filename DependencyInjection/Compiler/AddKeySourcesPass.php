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

class AddKeySourcesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('giftcards.encryption.key_source.chain')) {
            return;
        }
        
        $chain = $container->getDefinition('giftcards.encryption.key_source.chain');

        foreach ($container->findTaggedServiceIds('giftcards.encryption.key_source') as $id => $tags) {
            foreach ($tags as $tag) {
                $serviceId = $id;
                if (!empty($tag['prefix'])) {
                    $internalId = $serviceId;
                    $serviceId = sprintf('%s.prefixed.%s', $serviceId, $tag['prefix']);
                    $container
                        ->register($serviceId, 'Giftcards\Encryption\Key\PrefixKeyNameSource')
                        ->setArguments(array(
                            $tag['prefix'],
                            new Reference($internalId)
                        ))
                    ;
                }
                
                if (!empty($tag['add_circular_guard'])) {
                    $internalId = $serviceId;
                    $serviceId = sprintf('%s.circular_guarded', $serviceId);
                    $container
                        ->register($serviceId, 'Giftcards\Encryption\Key\CircularGuardSource')
                        ->setArguments(array(new Reference($internalId)))
                    ;
                }
                
                $chain->addMethodCall('addServiceId', array($serviceId));
            }
        }
    }
}