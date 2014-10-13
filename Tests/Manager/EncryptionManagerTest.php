<?php
namespace Omni\EncryptionBundle\Tests\Manager;

use Omni\EncryptionBundle\Manager\EncryptionManager;
use Omni\TestingBundle\TestCase\Extension\AbstractExtendableTestCase;
use Mockery;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class EncryptionManagerTest extends AbstractExtendableTestCase
{

    protected $manager;

    public function setUp()
    {
        $this->logger = \Mockery::mock('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface');
        $doctrine = \Mockery::mock('\\Doctrine\\Bundle\\DoctrineBundle\\Registry');

        $this->manager = new EncryptionManager($this->logger, $doctrine, "SuperSecretEncryptionString");
    }

    public function testAesEncryptDecrypt()
    {
        $stringToEncode = "I have a lovely bunch of coconuts.";
        $encoded = $this->manager->aesEncrypt($stringToEncode);
        $this->assertNotEquals($stringToEncode, $encoded);

        $decoded = $this->manager->aesDecrypt($encoded);
        $this->assertEquals($stringToEncode, $decoded);
    }

    public function testAesNullEncryptDecrypt()
    {
        $stringToEncode = null;
        $encoded = $this->manager->aesEncrypt($stringToEncode);
        $this->assertEquals(null, $encoded);

        $decoded = $this->manager->aesDecrypt(null);
        $this->assertEquals(null, $decoded);
    }

    public function testAesEmptyEncryptDecrypt()
    {
        $stringToEncode = '';
        $decoded = $this->manager->aesDecrypt($stringToEncode);
        $this->assertEquals('', $decoded);
    }

    public function testAesSpaceEncryptDecrypt()
    {
        $stringToEncode = ' ';
        $decoded = $this->manager->aesDecrypt($stringToEncode);
        $this->assertEquals($stringToEncode, $decoded);
    }

} 