<?php

namespace Giftcards\EncryptionBundle;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddBuildersPass;
use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass;
use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass;
use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersDeserializersPass;
use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GiftcardsEncryptionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new AddCipherTextRotatorsPass())
            ->addCompilerPass(new AddCiphersPass())
            ->addCompilerPass(new AddKeySourcesPass())
            ->addCompilerPass(new AddCipherTextSerializersDeserializersPass())
            ->addCompilerPass(new AddBuildersPass())
        ;
    }
}
