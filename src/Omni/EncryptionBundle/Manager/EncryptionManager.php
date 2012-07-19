<?php
namespace Omni\EncryptionBundle\Manager;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class EncryptionManager {

	protected $logger;
	protected $encryptionString;
	/**
	 * Property to contain our Registry to the Doctrine bundle
	 *
	 * @var type
	 */
	protected $doctrine;
	
	/**
	 *
	 * @param LoggerInterface $logger
	 * @param Registry $doctrine
	 */
	public function __construct(LoggerInterface $logger, Registry $doctrine, $encryptionString){
	
		$this->logger = $logger;
		$this->doctrine = $doctrine;
		$this->encryptionString = $encryptionString;
	}
	
	/**
	 * checks that the password is of the correct format
	 * 
	 * @param string $password
	 * @return boolean
	 */
	public function checkPasswordFormat($password) {
		// VERIFY THE PASSWORD IS OF THE CORRECT FORMAT
		if (strlen($password) < 8 || !preg_match('/[\d]+/',$password) || !preg_match('/[A-Z]+/',$password)){
			return false;
		}
		return true;
	}
	
	public function aesEncrypt($value) {
        if ($value === null){
        	return null;
        }
		return MCRYPT_ENCRYPT( MCRYPT_RIJNDAEL_256, $this->encryptionString, $value, MCRYPT_MODE_ECB, MCRYPT_CREATE_IV( MCRYPT_GET_IV_SIZE( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB ), MCRYPT_DEV_URANDOM ) );
    }

    public function aesDecrypt($value) {
        if ($value === null) {
        	return null;
        }
		
		return TRIM( MCRYPT_DECRYPT( MCRYPT_RIJNDAEL_256, $this->encryptionString, $value, MCRYPT_MODE_ECB, MCRYPT_CREATE_IV( MCRYPT_GET_IV_SIZE( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB ), MCRYPT_DEV_URANDOM ) ) );
    }
	
}
