<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/11/15
 * Time: 11:09 AM
 */

namespace Giftcards\EncryptionBundle\CipherText\Rotator\Factory;

use Doctrine\DBAL\Connection;
use Giftcards\Encryption\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilder as BaseDoctrineDBALRotatorBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoctrineDBALRotatorBuilder extends BaseDoctrineDBALRotatorBuilder
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
