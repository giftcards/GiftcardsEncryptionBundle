<?php
namespace Omni\EncryptionBundle\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class BlobType extends Type
{
	const BLOB = 'blog';

    public function getName()
    {
        return self::BLOB;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping('BLOB');
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : base64_encode($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : base64_decode($value);
    }
}