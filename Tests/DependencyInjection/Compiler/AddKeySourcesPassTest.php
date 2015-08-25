<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:37 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddKeySourcesPassTest extends AbstractExtendableTestCase
{
    /** @var  AddKeySourcesPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddKeySourcesPass();
    }

    public function testProcessWithNoChain()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testProcessWithChain()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('omni.encryption.key_source.chain', new Definition());
        $container->setDefinition('not_key_source', new Definition());
        $container->setDefinition('key_source1', new Definition())->addTag(
            'omni.encryption.key_source',
            array('prefix' => 'foo')
        );
        $container->setDefinition('key_source23', new Definition())
            ->addTag(
                'omni.encryption.key_source',
                array('prefix' => 'bar')
            )
            ->addTag(
                'omni.encryption.key_source'
            )
        ;
        $container->setDefinition('key_source4', new Definition())->addTag(
            'omni.encryption.key_source'
        );
        $this->pass->process($container);
        $this->assertContains(
            array('addServiceId', array('key_source1.prefixed.foo')),
            $container->getDefinition('omni.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source23.prefixed.bar')),
            $container->getDefinition('omni.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source23')),
            $container->getDefinition('omni.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source4')),
            $container->getDefinition('omni.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(new Definition(
            'Omni\Encryption\Key\PrefixKeyNameSource',
            array(
                'foo',
                new Reference('key_source1')
            )
        ), $container->getDefinition('key_source1.prefixed.foo'));
        $this->assertEquals(new Definition(
            'Omni\Encryption\Key\PrefixKeyNameSource',
            array(
                'bar',
                new Reference('key_source23')
            )
        ), $container->getDefinition('key_source23.prefixed.bar'));
    }
}
