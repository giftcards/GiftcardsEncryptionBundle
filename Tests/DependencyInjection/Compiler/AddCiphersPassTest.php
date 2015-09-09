<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:16 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass;
use Giftcards\Encryption\Tests\AbstractTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddCiphersPassTest extends AbstractTestCase
{
    /** @var  AddCiphersPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCiphersPass();
    }

    public function testProcessWithNoRegistry()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithRegistry()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_cipher', new Definition());
        $container->setDefinition('cipher1', new Definition())->addTag(
            'giftcards.encryption.cipher',
            array('alias' => 'cipher1')
        );
        $container->setDefinition('cipher2', new Definition())
            ->addTag(
                'giftcards.encryption.cipher',
                array('alias' => 'cipher2')
            )
        ;
        $container->setDefinition('cipher3', new Definition())->addTag(
            'giftcards.encryption.cipher',
            array('alias' => 'cipher3')
        );
        $this->pass->process($container);
        $this->assertContains(
            array('setServiceId', array('cipher1', 'cipher1')),
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('setServiceId', array('cipher2', 'cipher2')),
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('setServiceId', array('cipher3', 'cipher3')),
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls(),
            '',
            false,
            false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithRegistryAndAServiceWIthADoubleTag()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_cipher', new Definition());
        $container->setDefinition('cipher1', new Definition())
            ->addTag(
                'giftcards.encryption.cipher',
                array('alias' => 'cipher1')
            )
            ->addTag(
                'giftcards.encryption.cipher',
                array('alias' => 'cipher2')
            )
        ;
        $this->pass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithRegistryAndMissingAlias()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'giftcards.encryption.cipher'
        );
        $this->pass->process($container);
    }
}
