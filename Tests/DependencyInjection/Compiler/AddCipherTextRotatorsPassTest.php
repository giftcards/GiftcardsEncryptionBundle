<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:14 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddCipherTextRotatorsPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextRotatorsPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCipherTextRotatorsPass();
    }

    public function testProcessWithNoRegistry()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithRegistry()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('omni.encryption.cipher_text_rotator.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'omni.encryption.cipher_text_rotator',
            array('alias' => 'rotator1')
        );
        $container->setDefinition('rotator23', new Definition())
            ->addTag(
                'omni.encryption.cipher_text_rotator',
                array('alias' => 'rotator2')
            )
            ->addTag(
                'omni.encryption.cipher_text_rotator',
                array('alias' => 'rotator3')
            )
        ;
        $container->setDefinition('rotator4', new Definition())->addTag(
            'omni.encryption.cipher_text_rotator',
            array('alias' => 'rotator4')
        );
        $this->pass->process($container);
        $this->assertContains(
            array('setServiceId', array('rotator1', 'rotator1')),
            $container->getDefinition('omni.encryption.cipher_text_rotator.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('setServiceId', array('rotator2', 'rotator23')),
            $container->getDefinition('omni.encryption.cipher_text_rotator.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('setServiceId', array('rotator3', 'rotator23')),
            $container->getDefinition('omni.encryption.cipher_text_rotator.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('setServiceId', array('rotator4', 'rotator4')),
            $container->getDefinition('omni.encryption.cipher_text_rotator.registry')->getMethodCalls(),
            '',
            false,
            false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithRegistryAndMissingAlias()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('omni.encryption.cipher_text_rotator.registry', new Definition());
        $container->setDefinition('not_rotator', new Definition());
        $container->setDefinition('rotator1', new Definition())->addTag(
            'omni.encryption.cipher_text_rotator'
        );
        $this->pass->process($container);
    }
}
