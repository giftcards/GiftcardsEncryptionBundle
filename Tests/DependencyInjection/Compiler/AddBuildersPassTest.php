<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/10/15
 * Time: 5:46 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddBuildersPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddBuildersPassTest extends AbstractExtendableTestCase
{
    /** @var  AddBuildersPass */
    protected $pass;

    public function setUp() : void
    {
        $this->pass = new AddBuildersPass();
    }

    public function testProcessWithRegistries()
    {
        $this->expectNoException();
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.key_source.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_rotator.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_serializer.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_deserializer.factory.registry', new Definition());
        $container->setDefinition('key_source_builder', new Definition())
            ->addTag('giftcards.encryption.key_source.builder', ['alias' => 'source'])
        ;
        $container->setDefinition('cipher_text_rotator_builder', new Definition())
            ->addTag('giftcards.encryption.cipher_text_rotator.builder', ['alias' => 'rotator'])
        ;
        $container->setDefinition('cipher_text_serializer_builder', new Definition())
            ->addTag('giftcards.encryption.cipher_text_serializer.builder', ['alias' => 'serializer'])
        ;
        $container->setDefinition('cipher_text_deserializer_builder', new Definition())
            ->addTag('giftcards.encryption.cipher_text_deserializer.builder', ['alias' => 'deserializer'])
        ;
        $container->setDefinition('not_any', new Definition());
        $this->pass->process($container);
        $this->assertContainsEquals(
            ['setServiceId', ['source', 'key_source_builder']],
            $container->getDefinition('giftcards.encryption.key_source.factory.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['rotator', 'cipher_text_rotator_builder']],
            $container->getDefinition('giftcards.encryption.cipher_text_rotator.factory.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['serializer', 'cipher_text_serializer_builder']],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer.factory.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['deserializer', 'cipher_text_deserializer_builder']],
            $container->getDefinition('giftcards.encryption.cipher_text_deserializer.factory.registry')->getMethodCalls()
        );
    }

    public function testMissingAlias()
    {
        $this->expectException('\InvalidArgumentException');
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.key_source.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_rotator.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_serializer.factory.registry', new Definition());
        $container->setDefinition('giftcards.encryption.cipher_text_deserializer.factory.registry', new Definition());
        $container->setDefinition('key_source_builder', new Definition())
            ->addTag('giftcards.encryption.key_source.builder', ['alias' => 'source'])
        ;
        $container->setDefinition('cipher_text_rotator_builder', new Definition())
            ->addTag('giftcards.encryption.cipher_text_rotator.builder')
        ;
        $this->pass->process($container);
    }
}
