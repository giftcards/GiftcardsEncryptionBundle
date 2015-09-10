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
        $this->assertNull($container->getDefinition('giftcards.encryption.encryptor')->getArgument(4));
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
            array('addServiceId', array('giftcards.encryption.key_source.fallback')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\FallbackSource', array($fallbacks, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.fallback')
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
            array('addServiceId', array('giftcards.encryption.key_source.mapping')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\MappingSource', array($map, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.mapping')
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
            array('addServiceId', array('giftcards.encryption.key_source.combining')),
            $container->getDefinition('giftcards.encryption.key_source.chain')->getMethodCalls(),
            '',
            false,
            false
        );

        $this->assertEquals(
            new Definition('Giftcards\Encryption\Key\CombiningSource', array($combined, new Reference('giftcards.encryption.key_source'))),
            $container->getDefinition('giftcards.encryption.key_source.combining')
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
}
