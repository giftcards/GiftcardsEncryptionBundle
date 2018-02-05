<?php
/**
 * Created by PhpStorm.
 * User: jjose00
 * Date: 2/5/18
 * Time: 5:42 PM
 */

namespace Giftcards\EncryptionBundle\CipherText\Rotator\Store;

use Doctrine\DBAL\Connection;
use Giftcards\Encryption\CipherText\Rotator\Store\DoctrineDBALStoreBuilder as BaseDoctrineDBALStoreBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoctrineDBALStoreBuilder extends BaseDoctrineDBALStoreBuilder
{
    protected $container;

    /**
     * DatabaseTableRotatorBuilder constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function configureOptionsResolver(OptionsResolver $resolver)
    {
        parent::configureOptionsResolver($resolver);
        $container = $this->container;
        $resolver
            ->addAllowedTypes('connection', 'string')
            ->setNormalizer('connection', function ($_, $connection) use ($container) {
                if ($connection instanceof Connection) {
                    return $connection;
                }

                return $container->get($connection);
            })
        ;
    }
}