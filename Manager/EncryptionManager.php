<?php
namespace Omni\EncryptionBundle\Manager;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
        
		$value = $this->stringify($value);
		
		if ($value === null){
        	return null;
        }
		
        
        $pad_value = 16-(strlen($value) % 16);
        $value = str_pad($value, (16*(floor(strlen($value) / 16)+1)), chr($pad_value));
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->mysqlAesKey($this->encryptionString), $value, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
    }

    public function aesDecrypt($value) {
		
    	$value = $this->stringify($value);
        
        if ($value === null) {
        	return null;
        }
		
        $value = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->mysqlAesKey($this->encryptionString), $value, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
        return rtrim($value, "\x00..\x1F");
    }
    
    protected function mysqlAesKey($key) {
    	
    	$new_key = str_repeat(chr(0), 16);
       for($i=0,$len=strlen($key);$i<$len;$i++){
       	
           $new_key[$i%16] = $new_key[$i%16] ^ $key[$i];
       }
      
       return $new_key;
    }
    
    protected function stringify($value) {
    	
    	if (!is_resource($value)) {
    		
    		return $value;
    	}
    	
    	return stream_get_contents($value);
    }
	
}
