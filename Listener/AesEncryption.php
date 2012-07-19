<?php
// src/Omni/EncriptionBundle/Listener/CreditcardEncryption.php
namespace Omni\EncryptionBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Local\MerchantBundle\Entity\Creditcard;

class AesEncryption
{
	protected $encryptionManager;
	
	public function __construct($encryptionManager){
		$this->encryptionManager = $encryptionManager;
	}
	
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$entity->$setmethod($this->encryptionManager->aesEncrypt($entity->$getmethod()));
			}
		}
    }
	
	public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$entity->$setmethod($this->encryptionManager->aesEncrypt($entity->$getmethod()));
			}
		}
    }
	
	public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
		
		$factory = $entityManager->getMetadataFactory();
		$metadata = $factory->getMetadataFor(get_class($entity));
		
		foreach ($metadata->fieldMappings as $key => $attributes){
			if ($attributes['type'] == 'aescrypt'){
				$setmethod = 'set'.ucfirst($key);
				$getmethod = 'get'.ucfirst($key);
				$entity->$setmethod($this->encryptionManager->aesDecrypt($entity->$getmethod()));
			}
		}
		
    }
	
}