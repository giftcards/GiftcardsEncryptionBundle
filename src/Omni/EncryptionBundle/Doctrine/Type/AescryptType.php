<?php
namespace Omni\EncryptionBundle\Doctrine\Type;
use Omni\EncryptionBundle\Doctrine\Type\BlobType;

/**
 * Type that aes encrypts and decrypts.
 *
 * @author jdavis
 */
class AescryptType extends BlobType
{
	const AESCRYPT = 'aescrypt';

    public function getName()
    {
        return self::AESCRYPT;
    }
}