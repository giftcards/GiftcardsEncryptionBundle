<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:37 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass;
use Giftcards\Encryption\Tests\AbstractTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddKeySourcesPassTest extends AbstractTestCase
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
        $container->setDefinition('giftcards.encryption.key_source.chain', new Definition());
        $container->setDefinition('not_key_source', new Definition());
        $container->setDefinition('key_source1', new Definition())->addTag(
            'giftcards.encryption.key_source',
            array('prefix' => 'foo')
        );
        $container->setDefinition('key_source23', new Definition())
            ->addTag(
                'giftcards.encryption.key_source',
                array('prefix' => 'bar')
            )
            ->addTag(
                'giftcards.encryption.key_source'
            )
        ;
        $container->setDefinition('key_source4', new Definition())->addTag(
            'giftcards.encryption.key_source'
        );
        $container->setDefinition('key_source5', new Definition())->addTag(
            'giftcards.encryption.key_source',
            array('prefix' => 'baz', 'add_circular_guard' => true)
        );
        $this->pass->process($container);
        $this->assertContains(
            array('addServiceId', array('key_source1.prefixed.foo')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source23.prefixed.bar')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source23')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source4')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('addServiceId', array('key_source5.prefixed.baz.circular_guarded')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );

        $this->assertEquals(new Definition(
            'Giftcards\Encryption\Key\PrefixKeyNameSource',
            array(
                'foo',
                new Reference('key_source1')
            )
        ), $container->getDefinition('key_source1.prefixed.foo'));
        $this->assertEquals(new Definition(
            'Giftcards\Encryption\Key\PrefixKeyNameSource',
            array(
                'bar',
                new Reference('key_source23')
            )
        ), $container->getDefinition('key_source23.prefixed.bar'));
        $this->assertEquals(new Definition(
            'Giftcards\Encryption\Key\PrefixKeyNameSource',
            array(
                'baz',
                new Reference('key_source5')
            )
        ), $container->getDefinition('key_source5.prefixed.baz'));
        $this->assertEquals(new Definition(
            'Giftcards\Encryption\Key\CircularGuardSource',
            array(
                new Reference('key_source5.prefixed.baz')
            )
        ), $container->getDefinition('key_source5.prefixed.baz.circular_guarded'));
    }
}
