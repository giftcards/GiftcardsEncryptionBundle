<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:10 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersPass;
use Giftcards\Encryption\Tests\AbstractTestCase;
use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersDeserializersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddCipherTextSerializersDeserializersPassTest extends AbstractTestCase
{
    /** @var  AddCipherTextSerializersDeserializersPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCipherTextSerializersDeserializersPass();
    }
    
    public function testProcessWithNoChain()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithChains()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher_text_serializer.chain', new Definition());
        $container->setDefinition('not_serializer', new Definition());
        $container->setDefinition('serializer1', new Definition())->addTag(
            'giftcards.encryption.cipher_text_serializer',
            array('priority' => 56)
        );
        $container->setDefinition('serializer23', new Definition())
            ->addTag(
                'giftcards.encryption.cipher_text_serializer',
                array('priority' => 23)
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
            'omni.encryption.cipher_text_deserializer',
            array('priority' => 56)
        );
        $container->setDefinition('deserializer23', new Definition())
            ->addTag(
                'omni.encryption.cipher_text_deserializer',
                array('priority' => 23)
            )
            ->addTag(
                'omni.encryption.cipher_text_deserializer'
            )
        ;
        $container->setDefinition('deserializer4', new Definition())->addTag(
            'omni.encryption.cipher_text_deserializer'
        );
        $this->pass->process($container);
        $this->assertContains(
            array('addServiceId', array('serializer1', 56)),
            $container->getDefinition('giftcards.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer23', 23)),
            $container->getDefinition('giftcards.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer23', 0)),
            $container->getDefinition('giftcards.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer4', 0)),
            $container->getDefinition('giftcards.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('deserializer1', 56)),
            $container->getDefinition('omni.encryption.cipher_text_deserializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('deserializer23', 23)),
            $container->getDefinition('omni.encryption.cipher_text_deserializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('deserializer23', 0)),
            $container->getDefinition('omni.encryption.cipher_text_deserializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('deserializer4', 0)),
            $container->getDefinition('omni.encryption.cipher_text_deserializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
    }
}
