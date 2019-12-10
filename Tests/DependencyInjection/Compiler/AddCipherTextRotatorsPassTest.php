<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:14 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddCipherTextRotatorsPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextRotatorsPass */
    protected $pass;

    public function setUp() : void
    {
        $this->pass = new AddCipherTextRotatorsPass();
    }

    public function testProcessWithNoRegistry()
    {
        $this->expectNoException();
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithRegistry()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher_text_rotator.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'giftcards.encryption.cipher_text_rotator',
            ['alias' => 'rotator1']
        );
        $container->setDefinition('rotator23', new Definition())
            ->addTag(
                'giftcards.encryption.cipher_text_rotator',
                ['alias' => 'rotator2']
            )
            ->addTag(
                'giftcards.encryption.cipher_text_rotator',
                ['alias' => 'rotator3']
            )
        ;
        $container->setDefinition('rotator4', new Definition())->addTag(
            'giftcards.encryption.cipher_text_rotator',
            ['alias' => 'rotator4']
        );
        $this->pass->process($container);
        $this->assertContainsEquals(
            ['setServiceId', ['rotator1', 'rotator1']],
            $container->getDefinition('giftcards.encryption.cipher_text_rotator.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['rotator2', 'rotator23']],
            $container->getDefinition('giftcards.encryption.cipher_text_rotator.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['rotator3', 'rotator23']],
            $container->getDefinition('giftcards.encryption.cipher_text_rotator.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            ['setServiceId', ['rotator4', 'rotator4']],
            $container->getDefinition('giftcards.encryption.cipher_text_rotator.registry')->getMethodCalls()
        );
    }

    public function testProcessWithRegistryAndMissingAlias()
    {
        $this->expectException('\InvalidArgumentException');
        $container = new ContainerBuilder();
        $container->setDefinition('giftcards.encryption.cipher_text_rotator.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'giftcards.encryption.cipher_text_rotator'
        );
        $this->pass->process($container);
    }
}
