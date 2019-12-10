<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:10 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersDeserializersPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddCipherTextSerializersDeserializersPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextSerializersDeserializersPass */
    protected $pass;

    public
function setUp() : void
    {
        $this->pass = new AddCipherTextSerializersDeserializersPass();
    }
    
    public function testProcessWithNoChain()
    {
        $this->expectNoException();
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithChains()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain', new Definition());
        $container->setDefinition('not_serializer', new Definition());
        $container->setDefinition('serializer1', new Definition())->addTag(
            'giftcards.encryption.cipher_text_serializer',
            ['priority' => 56]
        );
        $container->setDefinition('serializer23', new Definition())
            ->addTag(
                'giftcards.encryption.cipher_text_serializer',
                ['priority' => 23]
            )
            ->addTag(
                'giftcards.encryption.cipher_text_serializer'
            )
        ;
        $container->setDefinition('serializer4', new Definition())->addTag(
            'giftcards.encryption.cipher_text_serializer'
        );
        $container->setDefinition('not_deserializer', new Definition());
        $container->setDefinition('deserializer1', new Definition())->addTag(
            'giftcards.encryption.cipher_text_deserializer',
            ['priority' => 56]
        );
        $container->setDefinition('deserializer23', new Definition())
            ->addTag(
                'giftcards.encryption.cipher_text_deserializer',
                ['priority' => 23]
            )
            ->addTag(
                'giftcards.encryption.cipher_text_deserializer'
            )
        ;
        $container->setDefinition('deserializer4', new Definition())->addTag(
            'giftcards.encryption.cipher_text_deserializer'
        );
        $this->pass->process($container);
        $this->assertContainsEquals(
            ['addSerializerServiceId', ['serializer1', 56]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addSerializerServiceId', ['serializer23', 23]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addSerializerServiceId', ['serializer23', 0]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addSerializerServiceId', ['serializer4', 0]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addDeserializerServiceId', ['deserializer1', 56]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addDeserializerServiceId', ['deserializer23', 23]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addDeserializerServiceId', ['deserializer23', 0]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['addDeserializerServiceId', ['deserializer4', 0]],
            $container->getDefinition('giftcards.encryption.cipher_text_serializer_deserializer.chain')->getMethodCalls()
        );
    }
}
