<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/25/15
 * Time: 6:53 PM
 */

namespace Giftcards\EncryptionBundle\Tests;

use Giftcards\Encryption\Tests\AbstractTestCase;
use Giftcards\EncryptionBundle\GiftcardsEncryptionBundle;

class GiftcardsEncryptionBundleTest extends AbstractTestCase
{
    /** @var  GiftcardsEncryptionBundle */
    protected $bundle;

    public function setUp()
    {
        $this->bundle = new GiftcardsEncryptionBundle();
    }

    public function testCompile()
    {
        $this->bundle->build(
            \Mockery::mock('Symfony\Component\DependencyInjection\ContainerBuilder')
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextRotatorsPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextSerializersDeserializersPass')
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addCompilerPass')
                ->once()
                ->with('Giftcards\EncryptionBundle\DependencyInjection\Compiler\AddKeySourcesPass')
                ->andReturn(\Mockery::self())
                ->getMock()
        );
    }
}
