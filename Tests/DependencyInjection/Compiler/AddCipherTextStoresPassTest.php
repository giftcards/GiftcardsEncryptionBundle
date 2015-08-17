<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 8/6/15
 * Time: 3:14 PM
 */

namespace Omni\EncryptionBundle\Tests\DependencyInjection\Compiler;

use Omni\EncryptionBundle\DependencyInjection\Compiler\AddCipherTextGroupStoresPass;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddCipherTextStoresPassTest extends AbstractExtendableTestCase
{
    /** @var  AddCipherTextGroupStoresPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new AddCipherTextGroupStoresPass();
    }

    public function testCompileWithNoRegistry()
    {
        $this->pass->process(new ContainerBuilder());
    }
}
