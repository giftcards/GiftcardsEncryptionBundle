<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:16 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddCiphersPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCiphersPass */
    protected $pass;

    public function setUp() : void
    {
        $this->pass = new AddCiphersPass();
    }

    public function testProcessWithNoRegistry()
    {
        $this->expectNoException();
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithRegistry()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_cipher', new Definition());
        $container->setDefinition('cipher1', new Definition())->addTag(
            'giftcards.encryption.cipher',
            ['alias' => 'cipher1']
        );
        $container->setDefinition('cipher2', new Definition())
            ->addTag(
                'giftcards.encryption.cipher',
                ['alias' => 'cipher2']
            )
        ;
        $container->setDefinition('cipher3', new Definition())->addTag(
            'giftcards.encryption.cipher',
            ['alias' => 'cipher3']
        );
        $this->pass->process($container);
        $this->assertContainsEquals(
            ['setServiceId', ['cipher1', 'cipher1']],
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['cipher2', 'cipher2']],
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['cipher3', 'cipher3']],
            $container->getDefinition('giftcards.encryption.cipher.registry')->getMethodCalls()
        );
    }

    public function testProcessWithRegistryAndAServiceWIthADoubleTag()
    {
        $this->expectException('\InvalidArgumentException');
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_cipher', new Definition());
        $container->setDefinition('cipher1', new Definition())
            ->addTag(
                'giftcards.encryption.cipher',
                ['alias' => 'cipher1']
            )
            ->addTag(
                'giftcards.encryption.cipher',
                ['alias' => 'cipher2']
            )
        ;
        $this->pass->process($container);
    }

    public function testProcessWithRegistryAndMissingAlias()
    {
        $this->expectException('\InvalidArgumentException');
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'giftcards.encryption.cipher'
        );
        $this->pass->process($container);
    }
}
