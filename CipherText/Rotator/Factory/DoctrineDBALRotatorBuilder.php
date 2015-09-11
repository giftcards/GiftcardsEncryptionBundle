<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/11/15
 * Time: 11:09 AM
 */

namespace Giftcards\EncryptionBundle\CipherText\Rotator\Factory;

use Giftcards\Encryption\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilder as BaseDoctrineDBALRotatorBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\DBAL\Conection;

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
        $container = $this->container;
        parent::configureOptionsResolver($resolver);
        $resolver
            ->addAllowedTypes(array('connection' => 'string'))
            ->setNormalizers(array('connection' => function ($_, $connection) use ($container) {
                if ($connection instanceof Connection) {
                    return $connection;
                }
                
                return $this->container->get($connection);
            }))
        ;
    }
}
