<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:14 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextStoresPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddCipherTextStoresPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextStoresPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCipherTextStoresPass();
    }

    public function testCompileWithNoRegistry()
    {
        $this->pass->process(new ContainerBuilder());
    }
}
