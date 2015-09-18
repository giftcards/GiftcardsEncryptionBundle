<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:46 PM
 */

namespace Giftcards\EncryptionBundle\Tests\DependencyInjection;

use Giftcards\Encryption\Key\CombiningSource;
use Giftcards\EncryptionBundle\DependencyInjection\GiftcardsEncryptionExtension;
use Giftcards\Encryption\Tests\AbstractTestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class GiftcardsEncryptionExtensionTest extends AbstractTestCase
{
    /** @var  GiftcardsEncryptionExtension */
    protected $extension;

    public function setUp()
    {
        $this->extension = new GiftcardsEncryptionExtension();
    }

    public function testLoad()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array(), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/services.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/factories.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/builders.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/doctrine_encrypted_properties.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertNull($container->getDefinition('giftcards.encryption.encryptor')->getArgument(4));
        $this->assertEquals(array(
            array('connection' => 'default')
        ), $container->getDefinition('giftcards.encryption.listener.encrypted_listener')->getTag('doctrine.event_subscriber'));
    }

    public function testLoadWhereDefaultProfileIsSet()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array(array('default_profile' => 'default')), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/services.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(
            'default',
            $container->getDefinition('giftcards.encryption.encryptor')->getArgument(4)
        );
    }

    public function testLoadWhereDefaultProfilesConfigured()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array(array(
            'profiles' => array(
                'foo' => array(
                    'cipher' => 'cipher1',
                    'key_name' => 'key1'
                ),
                'bar' => array(
                    'cipher' => 'cipher2',
                    'key_name' => 'key2'
                ),
            )
        )), $container);
        $this->assertContains(
            array('set', array('foo', new Definition(
                'Giftcards\Encryption\Profile\Profile',
                array('cipher1', 'key1')
            ))),
            $container->getDefinition('giftcards.encryption.profile.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('set', array('bar', new Definition(
                'Giftcards\Encryption\Profile\Profile',
                array('cipher2', 'key2')
            ))),
            $container->getDefinition('giftcards.encryption.profile.registry')->getMethodCalls(),
            '',
            false,
            false
        );
    }

    public function testLoadWhereKeyFallbacksAreConfigured()
    {
        $container = new ContainerBuilder();
        $fallbacks = array(
            'key1' => array(
                'key3',
                'key4'
            ),
            'key2' => array(
                'key5',
                'key6',
                'key7',
            )
        );
        $this->extension->load(array(array(
            'keys' => array(
                'fallbacks' => $fallbacks,
            )
        )), $container);
        $this->assertContains(
            array('addServiceId', array('giftcards.encryption.key_source.fallback.circular_guard')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\FallbackSource', array($fallbacks, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.fallback')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', array(new Reference('giftcards.encryption.key_source.fallback'))),
            $container->getDefinition('giftcards.encryption.key_source.fallback.circular_guard')
        );
    }

    public function testLoadWhereKeyMapIsConfigured()
    {
        $container = new ContainerBuilder();
        $map = array(
            'key1' => 'key3',
            'key2' => 'key5',
        );
        $this->extension->load(array(array(
            'keys' => array(
                'map' => $map,
            )
        )), $container);
        $this->assertContains(
            array('addServiceId', array('giftcards.encryption.key_source.mapping.circular_guard')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\MappingSource', array($map, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.mapping')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', array(new Reference('giftcards.encryption.key_source.mapping'))),
            $container->getDefinition('giftcards.encryption.key_source.mapping.circular_guard')
        );
    }

    public function testLoadWhereCombinedKeysAreConfigured()
    {
        $container = new ContainerBuilder();
        $combined = array(
            $this->getFaker()->unique()->word => array(
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ),
            $this->getFaker()->unique()->word => array(
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ),
            $this->getFaker()->unique()->word => array(
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ),
        );
        $this->extension->load(array(array(
            'keys' => array(
                'combine' => $combined,
            )
        )), $container);
        $this->assertContains(
            array('addServiceId', array('giftcards.encryption.key_source.combining.circular_guard')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CombiningSource', array($combined, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.combining')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', array(new Reference('giftcards.encryption.key_source.combining'))),
            $container->getDefinition('giftcards.encryption.key_source.combining.circular_guard')
        );
    }

    public function testLoadWhereCacheIsTrue()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array(array(
            'keys' => array(
                'cache' => true,
            )
        )), $container);
        $this->assertEquals('giftcards.encryption.key_source.caching', $container->getAlias('giftcards.encryption.key_source'));
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CachingSource', array(
                new Reference('giftcards.encryption.key_source.chain'),
                new Definition('Doctrine\Common\Cache\ArrayCache')
            )),
            $container->getDefinition('giftcards.encryption.key_source.caching')
        );
    }

    public function testLoadWhereSourcesAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = array(
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        );
        $prefix = $this->getFaker()->unique()->word;
        $addCircularGuard = $this->getFaker()->boolean();
        $this->extension->load(array(array(
            'keys' => array(
                'sources' => array(
                    array(
                        'type' => $type,
                        'options' => $options,
                        'prefix' => $prefix,
                        'add_circular_guard' => $addCircularGuard
                    )
                ),
            )
        )), $container);
        $definition = new DefinitionDecorator('giftcards.encryption.abstract_key_source');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.key_source', array(
                'prefix' => $prefix,
                'add_circular_guard' => $addCircularGuard
            ))
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.key_source.0'));
    }

    public function testLoadWhereCipherTextRotatorsAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = array(
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        );
        $prefix = $this->getFaker()->unique()->word;
        $name = $this->getFaker()->unique()->word;
        $this->extension->load(array(array(
            'cipher_texts' => array(
                'rotators' => array(
                    $name => array(
                        'type' => $type,
                        'options' => $options,
                    )
                ),
            )
        )), $container);
        $definition = new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_rotator');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_rotator', array('alias' => $name))
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_rotator.'.$name));
    }
    
    public function testLoadWhereCipherTextSerializersAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = array(
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        );
        $priority = $this->getFaker()->randomNumber();
        $this->extension->load(array(array(
            'cipher_texts' => array(
                'serializers' => array(
                    array(
                        'type' => $type,
                        'options' => $options,
                        'priority' => $priority
                    )
                ),
            )
        )), $container);
        $definition = new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_serializer');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_serializer', array('priority' => $priority))
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_serializer.0'));
    }
    
    public function testLoadWhereCipherTextDeserializersAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = array(
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        );
        $priority = $this->getFaker()->randomNumber();
        $this->extension->load(array(array(
            'cipher_texts' => array(
                'deserializers' => array(
                    array(
                        'type' => $type,
                        'options' => $options,
                        'priority' => $priority
                    )
                ),
            )
        )), $container);
        $definition = new DefinitionDecorator('giftcards.encryption.abstract_cipher_text_deserializer');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_deserializer', array('priority' => $priority))
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_deserializer.0'));
    }

    public function testLoadWhereEncryptedFieldsDisabled()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array(array(
            'doctrine' => array(
                'encrypted_properties' => array(
                    'enabled' => false
                )
            )
        )), $container);
        $this->assertNotContains(
            new FileResource(__DIR__.'/../../Resources/config/doctrine_encrypted_properties.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
    }

    public function testLoadWhereEncryptedFieldsHasConnectionsConfigured()
    {
        $container = new ContainerBuilder();
        $connection1 = $this->getFaker()->unique()->word;
        $connection2 = $this->getFaker()->unique()->word;
        $connection3 = $this->getFaker()->unique()->word;
        $this->extension->load(array(array(
            'doctrine' => array(
                'encrypted_properties' => array(
                    'connections' => array(
                        $connection1,
                        $connection2,
                        $connection3,
                    )
                )
            )
        )), $container);
        $this->assertEquals(array(
            array('connection' => $connection1),
            array('connection' => $connection2),
            array('connection' => $connection3),
        ), $container->getDefinition('giftcards.encryption.listener.encrypted_listener')->getTag('doctrine.event_subscriber'));
    }
}
