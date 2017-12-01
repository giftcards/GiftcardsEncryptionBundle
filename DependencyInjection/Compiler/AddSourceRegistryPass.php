<?php
/**
 * Created by PhpStorm.
 * User: jjose00
 * Date: 12/1/17
 * Time: 11:27 AM
 */

namespace Giftcards\EncryptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddSourceRegistryPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has("giftcards.encryption.store_registry_builder")) {
            return;
        }

        $storeRegistryBuilder = $container->findDefinition("giftcards.encryption.store_registry_builder");

        $storeIds = $container->findTaggedServiceIds("giftcards.encryption.store");
        foreach ($storeIds as $storeId) {
            $store = $container->get($storeId);
            $storeRegistryBuilder->addMethodCall("addStore", array(
                $storeId,
                $store
            ));
        }

    }
}
