<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:16 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddCiphersPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddCiphersPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCiphersPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCiphersPass();
    }

    public function testCompileWithNoRegistry()
    {
        $this->pass->process(new ContainerBuilder());
    }

    public function testCompileWithRegistry()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('omni.encryption.cipher.registry', new Definition());
        $container->setDefinition('cipher1', new Definition())->addTag('omni.encryption.cipher');
        $this->pass->process($container);
    }
}
