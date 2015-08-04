<?php

namespace Omni\EncryptionBundle;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddEncryptedDataStoresPass;
use Omni\EncryptionBundle\DependencyInjection\Compiler\AddEncryptorsPass;
use Omni\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OmniEncryptionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new AddEncryptedDataStoresPass())
            ->addCompilerPass(new AddEncryptorsPass())
            ->addCompilerPass(new AddKeySourcesPass())
        ;
    }
}
