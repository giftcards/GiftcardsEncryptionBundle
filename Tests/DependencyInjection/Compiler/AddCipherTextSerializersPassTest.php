<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:10 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddCipherTextSerializersPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextSerializersPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCipherTextSerializersPass();
    }
    
    public function testProcessWithNoChain()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithChain()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('omni.encryption.cipher_text_serializer.chain', new Definition());
        $container->setDefinition('not_serializer', new Definition());
        $container->setDefinition('serializer1', new Definition())->addTag(
            'omni.encryption.cipher_text_serializer',
            array('priority' => 56)
        );
        $container->setDefinition('serializer23', new Definition())
            ->addTag(
                'omni.encryption.cipher_text_serializer',
                array('priority' => 23)
            )
            ->addTag(
                'omni.encryption.cipher_text_serializer'
            )
        ;
        $container->setDefinition('serializer4', new Definition())->addTag(
            'omni.encryption.cipher_text_serializer'
        );
        $this->pass->process($container);
        $this->assertContains(
            array('addServiceId', array('serializer1', 56)),
            $container->getDefinition('omni.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer23', 23)),
            $container->getDefinition('omni.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer23', 0)),
            $container->getDefinition('omni.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('serializer4', 0)),
            $container->getDefinition('omni.encryption.cipher_text_serializer.chain')->getMethodCalls(),
            '',
            false,
            false
        );
    }

}
