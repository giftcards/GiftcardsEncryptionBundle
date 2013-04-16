<?php
// src/Omni/EncriptionBundle/Listener/CreditcardEncryption.php
namespace Omni\EncryptionBundle\Listener;

use Omni\EncryptionBundle\Manager\EncryptionManager;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Local\MerchantBundle\Entity\Creditcard;

class AesEncryption
{
	protected $encryptionManager;
	protected $encrypted;
	protected $decrypted;
	
	public function __construct(EncryptionManager $encryptionManager){
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
    	$metadata = $entityManager->getMetadataFactory()->getMetadataFor(get_class($entity));
        		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$encryptedValue = $this->encryptionManager->aesEncrypt($metadata->reflFields[$key]->getValue($entity));
				$metadata->reflFields[$key]->setValue($entity, $encryptedValue);
				$this->encrypted->attach($entity);
			}
		}
    }
	
	public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if($this->encrypted->contains($entity)){
        	
        	return;
        }
        
        $entityManager = $args->getEntityManager();
    	$metadata = $entityManager->getMetadataFactory()->getMetadataFor(get_class($entity));
        		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$encryptedValue = $this->encryptionManager->aesEncrypt($metadata->reflFields[$key]->getValue($entity));
				$metadata->reflFields[$key]->setValue($entity, $encryptedValue);
								
				if ($args->hasChangedField($key)) {
					
					$args->setNewValue($key, $encryptedValue);
				}
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
    	$metadata = $entityManager->getMetadataFactory()->getMetadataFor(get_class($entity));
				
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$encryptedValue = $this->encryptionManager->aesDecrypt($metadata->reflFields[$key]->getValue($entity));
				$metadata->reflFields[$key]->setValue($entity, $encryptedValue);
				$this->decrypted->attach($entity);
			}
		}
    }
	
    public function postUpdate(LifecycleEventArgs $args) {
    	
    	return $this->postSave($args);
    }
    
    public function postPersist(LifecycleEventArgs $args) {
    	
    	return $this->postSave($args);
    }
    
    public function postSave(LifecycleEventArgs $args) {
    	
    	$entity = $args->getEntity();
    	
    	if (!$this->encrypted->contains($entity)) {
    		
    		return;
    	}
    	
    	$entityManager = $args->getEntityManager();
    	
    	$metadata = $entityManager->getMetadataFactory()->getMetadataFor(get_class($entity));
    	
    	foreach ($metadata->fieldMappings as $key => $attributes){
    		if ($attributes['type'] == 'aescrypt'){
				$encryptedValue = $this->encryptionManager->aesDecrypt($metadata->reflFields[$key]->getValue($entity));
				$metadata->reflFields[$key]->setValue($entity, $encryptedValue);
       			$this->decrypted->attach($entity);
    		}
    	}
    	
    	$this->encrypted->detach($entity);
    }
}