<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:46 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection;

use Omni\EncryptionBundle\DependencyInjection\OmniEncryptionExtension;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class OmniEncryptionExtensionTest extends AbstractExtendableTestCase
{
    /** @var  OmniEncryptionExtension */
    protected $extension;

    public function setUp()
    {
        $this->extension = new OmniEncryptionExtension();
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
        $this->assertNull($container->getDefinition('omni.encryption.encryptor')->getArgument(4));
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
            $container->getDefinition('omni.encryption.encryptor')->getArgument(4)
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
                'Omni\Encryption\Profile\Profile',
                array('cipher1', 'key1')
            ))),
            $container->getDefinition('omni.encryption.profile.registry')->getMethodCalls(),
            '',
            false,
            false
        );
        $this->assertContains(
            array('set', array('bar', new Definition(
                'Omni\Encryption\Profile\Profile',
                array('cipher2', 'key2')
            ))),
            $container->getDefinition('omni.encryption.profile.registry')->getMethodCalls(),
            '',
            false,
            false
        );
    }
}
