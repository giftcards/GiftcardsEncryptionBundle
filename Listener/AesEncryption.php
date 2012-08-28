<?php
// src/Omni/EncriptionBundle/Listener/CreditcardEncryption.php
namespace Omni\EncryptionBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Local\MerchantBundle\Entity\Creditcard;

class AesEncryption
{
	protected $encryptionManager;
	protected $encrypted;
	protected $decrypted;
	
	public function __construct($encryptionManager){
		$this->encryptionManager = $encryptionManager;
		$this->encrypted = new \SplObjectStorage();
		$this->decrypted = new \SplObjectStorage();
	}
	
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if($this->encrypted->contains($entity)){
        	
        	return;
        }
        
        $entityManager = $args->getEntityManager();

        $factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$entity->$setmethod($this->encryptionManager->aesEncrypt($entity->$getmethod()));
				$this->encrypted->attach($entity);
			}
		}
    }
	
	public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if($this->encrypted->contains($entity)){
        	
        	return;
        }
        
        $entityManager = $args->getEntityManager();

        $factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$newValue = $this->encryptionManager->aesEncrypt($entity->$getmethod());
				$entity->$setmethod($newValue);
				$args->setNewValue($key, $value);
				$this->encrypted->attach($entity);
			}
		}
    }
	
	public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($this->decrypted->contains($entity)) {
        	
        	return;
        }
        
        $entityManager = $args->getEntityManager();
		
		$factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$entity->$setmethod($this->encryptionManager->aesDecrypt($entity->$getmethod()));
				$this->decrypted->attach($entity);
			}
		}
		
    }
	
}