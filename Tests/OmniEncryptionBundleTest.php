<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:53 PM
 */

namespace Omni\EncryptionBundle\Tests;

use Omni\EncryptionBundle\OmniEncryptionBundle;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;

class OmniEncryptionBundleTest extends AbstractExtendableTestCase
{
    /** @var  OmniEncryptionBundle */
    protected $bundle;

    public function setUp()
    {
        $this->bundle = new OmniEncryptionBundle();
    }

    public function testCompile()
    {
        $this->bundle->build(
            \Mockery::mock('Symfony\Component\DependencyInjection\ContainerBuilder')
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Omni\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Omni\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass')
                ->andReturn(\Mockery::self())
                ->getMock()
        );
    }
}
