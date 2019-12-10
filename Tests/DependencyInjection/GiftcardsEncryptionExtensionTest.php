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
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GiftcardsEncryptionExtensionTest extends AbstractExtendableTestCase
{
    /** @var  GiftcardsEncryptionExtension */
    protected $extension;

    public function setUp() : void
    {
        $this->extension = new GiftcardsEncryptionExtension();
    }

    public function testLoad()
    {
        $container = new ContainerBuilder();
        $this->extension->load([], $container);
        $this->assertContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/services.yml'),
            $container->getResources()
        );
        $this->assertContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/factories.yml'),
            $container->getResources()
        );
        $this->assertContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/builders.yml'),
            $container->getResources()
        );
        $this->assertContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/doctrine_encrypted_properties.yml'),
            $container->getResources()
        );
        $this->assertNull($container->getDefinition('giftcards.encryption.encryptor')->getArgument(4));
        $this->assertEquals([
            ['connection' => 'default']
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.default')->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => 'default']
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.odm.default')->getTag('doctrine_mongodb.odm.event_subscriber'));
    }

    public function testLoadWhereDefaultProfileIsSet()
    {
        $container = new ContainerBuilder();
        $this->extension->load([['default_profile' => 'default']], $container);
        $this->assertContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/services.yml'),
            $container->getResources()
        );
        $this->assertEquals(
            'default',
            $container->getDefinition('giftcards.encryption.encryptor')->getArgument(4)
        );
    }

    public function testLoadWhereDefaultProfilesConfigured()
    {
        $container = new ContainerBuilder();
        $this->extension->load([
            [
              'profiles' => [
                    'foo' => [
                        'cipher' => 'cipher1',
                        'key_name' => 'key1'
                    ],
                    'bar' => [
                        'cipher' => 'cipher2',
                        'key_name' => 'key2'
                    ],
                ]
            ]
        ], $container);
        $this->assertContainsEquals(
            [
                'set', [
                   'foo', new Definition(
                        'Giftcards\Encryption\Profile\Profile',
                        ['cipher1', 'key1']
                    )
                ]
            ],
            $container->getDefinition('giftcards.encryption.profile.registry')->getMethodCalls()
        );
        $this->assertContainsEquals(
            [
                'set', [
                'bar', new Definition(
                'Giftcards\Encryption\Profile\Profile',
                ['cipher2', 'key2']
            )
            ]
            ],
            $container->getDefinition('giftcards.encryption.profile.registry')->getMethodCalls()
        );
    }

    public function testLoadWhereKeyFallbacksAreConfigured()
    {
        $container = new ContainerBuilder();
        $fallbacks = [
            'key1' => [
                'key3',
                'key4'
            ],
            'key2' => [
                'key5',
                'key6',
                'key7',
            ]
        ];
        $this->extension->load([
            [
            'keys' => [
                'fallbacks' => $fallbacks,
            ]
            ]
        ], $container);
        $this->assertContainsEquals(
            ['addServiceId', ['giftcards.encryption.key_source.fallback.circular_guard']],
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls()
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\FallbackSource', [$fallbacks, new Reference('giftcards.encryption.key_source')]),
            $container->getDefinition('giftcards.encryption.key_source.fallback')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', [new Reference('giftcards.encryption.key_source.fallback')]),
            $container->getDefinition('giftcards.encryption.key_source.fallback.circular_guard')
        );
    }

    public function testLoadWhereKeyMapIsConfigured()
    {
        $container = new ContainerBuilder();
        $map = [
            'key1' => 'key3',
            'key2' => 'key5',
        ];
        $this->extension->load([
            [
                'keys' => [
                    'map' => $map,
                ]
            ]
        ], $container);
        $this->assertContainsEquals(
            ['addServiceId', ['giftcards.encryption.key_source.mapping.circular_guard']],
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls()
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\MappingSource', [$map, new Reference('giftcards.encryption.key_source')]),
            $container->getDefinition('giftcards.encryption.key_source.mapping')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', [new Reference('giftcards.encryption.key_source.mapping')]),
            $container->getDefinition('giftcards.encryption.key_source.mapping.circular_guard')
        );
    }

    public function testLoadWhereCombinedKeysAreConfigured()
    {
        $container = new ContainerBuilder();
        $combined = [
            $this->getFaker()->unique()->word => [
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ],
            $this->getFaker()->unique()->word => [
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ],
            $this->getFaker()->unique()->word => [
                CombiningSource::LEFT => $this->getFaker()->unique()->word,
                CombiningSource::RIGHT => $this->getFaker()->unique()->word,
            ],
        ];
        $this->extension->load([
            [
                'keys' => [
                    'combine' => $combined,
                ]
            ]
        ], $container);
        $this->assertContainsEquals(
            ['addServiceId', ['giftcards.encryption.key_source.combining.circular_guard']],
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls()
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CombiningSource', [$combined, new Reference('giftcards.encryption.key_source')]),
            $container->getDefinition('giftcards.encryption.key_source.combining')
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CircularGuardSource', [new Reference('giftcards.encryption.key_source.combining')]),
            $container->getDefinition('giftcards.encryption.key_source.combining.circular_guard')
        );
    }

    public function testLoadWhereCacheIsTrue()
    {
        $container = new ContainerBuilder();
        $this->extension->load([
            [
                'keys' => [
                    'cache' => true,
                ]
            ]
        ], $container);
        $this->assertEquals('giftcards.encryption.key_source.caching', $container->getAlias('giftcards.encryption.key_source'));
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CachingSource', [
                new Definition('Doctrine\Common\Cache\ArrayCache'),
                new Reference('giftcards.encryption.key_source.chain')
            ]),
            $container->getDefinition('giftcards.encryption.key_source.caching')
        );
    }

    public function testLoadWhereSourcesAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = [
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        ];
        $prefix = $this->getFaker()->unique()->word;
        $addCircularGuard = $this->getFaker()->boolean();
        $this->extension->load([
            [
                'keys' => [
                    'sources' => [
                        [
                            'type' => $type,
                            'options' => $options,
                            'prefix' => $prefix,
                            'add_circular_guard' => $addCircularGuard
                        ]
                    ],
                ]
            ]
        ], $container);
        $definition = new ChildDefinition('giftcards.encryption.abstract_key_source');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.key_source', [
                'prefix' => $prefix,
                'add_circular_guard' => $addCircularGuard
            ])
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.key_source.0'));
    }

    public function testLoadWhereCipherTextRotatorsAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = [
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        ];
        $name = $this->getFaker()->unique()->word;
        $this->extension->load([
            [
                'cipher_texts' => [
                    'rotators' => [
                        $name => [
                            'type' => $type,
                            'options' => $options,
                        ]
                    ],
                ]
            ]
        ], $container);
        $definition = new ChildDefinition('giftcards.encryption.abstract_cipher_text_rotator');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_rotator', ['alias' => $name])
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_rotator.'.$name));
    }
    
    public function testLoadWhereCipherTextSerializersAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = [
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        ];
        $priority = $this->getFaker()->randomNumber();
        $this->extension->load([
            [
                'cipher_texts' => [
                    'serializers' => [
                        [
                            'type' => $type,
                            'options' => $options,
                            'priority' => $priority
                        ]
                    ],
                ]
            ]
        ], $container);
        $definition = new ChildDefinition('giftcards.encryption.abstract_cipher_text_serializer');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_serializer', ['priority' => $priority])
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_serializer.0'));
    }
    
    public function testLoadWhereCipherTextDeserializersAreConfigured()
    {
        $container = new ContainerBuilder();
        $type = $this->getFaker()->unique()->word;
        $options = [
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
            $this->getFaker()->unique()->word => $this->getFaker()->unique()->word,
        ];
        $priority = $this->getFaker()->randomNumber();
        $this->extension->load([
            [
            'cipher_texts' => [
                'deserializers' => [
                    [
                        'type' => $type,
                        'options' => $options,
                        'priority' => $priority
                    ]
                ],
            ]
            ]
        ], $container);
        $definition = new ChildDefinition('giftcards.encryption.abstract_cipher_text_deserializer');
        $definition
            ->replaceArgument(0, $type)
            ->replaceArgument(1, $options)
            ->addTag('giftcards.encryption.cipher_text_deserializer', ['priority' => $priority])
        ;
        $this->assertEquals($definition, $container->getDefinition('giftcards.encryption.cipher_text_deserializer.0'));
    }

    public function testLoadWhereEncryptedFieldsDisabled()
    {
        $container = new ContainerBuilder();
        $this->extension->load([
            [
                'doctrine' => [
                    'encrypted_properties' => [
                        'enabled' => false
                    ]
                ]
            ]
        ], $container);
        $this->assertNotContainsEquals(
            new FileResource(__DIR__.'/../../Resources/config/doctrine_encrypted_properties.yml'),
            $container->getResources()
        );
    }

    public function testLoadWhereEncryptedFieldsHasConnectionsConfigured()
    {
        $container = new ContainerBuilder();
        $connection1 = $this->getFaker()->unique()->word;
        $connection2 = $this->getFaker()->unique()->word;
        $connection3 = $this->getFaker()->unique()->word;
        $this->extension->load([
            [
                'doctrine' => [
                    'encrypted_properties' => [
                        'connections' => [
                            $connection1,
                            $connection2,
                            $connection3,
                        ]
                    ]
                ]
            ]
        ], $container);
        $this->assertEquals([
            ['connection' => $connection1],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection1)->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection2],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection2)->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection3],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection3)->getTag('doctrine.event_subscriber'));
    }

    public function testLoadWhereEncryptedFieldsHasConnectionsConfiguredForOrmAndOdm()
    {
        $container = new ContainerBuilder();
        $connection1 = $this->getFaker()->unique()->word;
        $connection2 = $this->getFaker()->unique()->word;
        $connection3 = $this->getFaker()->unique()->word;
        $connection4 = $this->getFaker()->unique()->word;
        $connection5 = $this->getFaker()->unique()->word;
        $this->extension->load([
            [
                'doctrine' => [
                    'encrypted_properties' => [
                        'orm' => [
                            'connections' => [
                                $connection1,
                                $connection2,
                                $connection3,
                            ]
                        ],
                        'odm' => [
                            'connections' => [
                                $connection1,
                                $connection4,
                                $connection5,
                            ]
                        ],
                    ]
                ]
            ]
        ], $container);
        $this->assertEquals([
            ['connection' => $connection1],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection1)->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection2],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection2)->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection3],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.orm.'.$connection3)->getTag('doctrine.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection1],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.odm.'.$connection1)->getTag('doctrine_mongodb.odm.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection4],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.odm.'.$connection4)->getTag('doctrine_mongodb.odm.event_subscriber'));
        $this->assertEquals([
            ['connection' => $connection5],
        ], $container->getDefinition('giftcards.encryption.listener.encrypted_listener.odm.'.$connection5)->getTag('doctrine_mongodb.odm.event_subscriber'));
    }
}
