<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:53 PM
 */

namespace Giftcards\EncryptionBundle\Tests;


use Giftcards\EncryptionBundle\GiftcardsEncryptionBundle;
use Mockery;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;

class GiftcardsEncryptionBundleTest extends AbstractExtendableTestCase
{
    /** @var  GiftcardsEncryptionBundle */
    protected $bundle;

    public
function setUp() : void
    {
        $this->bundle = new GiftcardsEncryptionBundle();
    }

    public function testCompile()
    {
        $this->bundle->build(
            Mockery::mock('Symfony\Component\DependencyInjection\ContainerBuilder')
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersDeserializersPass')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddBuildersPass')
                ->andReturnSelf()
                ->getMock()
        );
    }
}
