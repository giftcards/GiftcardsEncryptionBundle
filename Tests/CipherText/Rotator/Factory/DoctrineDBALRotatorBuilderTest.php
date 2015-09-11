<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/11/15
 * Time: 12:20 PM
 */

namespace Giftcards\EncryptionBundle\Tests\CipherText\Rotator\Factory;

use Doctrine\DBAL\Connection;
use Giftcards\Encryption\Tests\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilderTest as BaseDoctrineDBALRotatorBuilderTest;
use Giftcards\Encryption\Tests\Mock\Mockery\Matcher\EqualsMatcher;
use Giftcards\EncryptionBundle\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @property DoctrineDBALRotatorBuilder $builder
 */
class DoctrineDBALRotatorBuilderTest extends BaseDoctrineDBALRotatorBuilderTest
{
    /** @var  ContainerInterface */
    protected $container;

    public function setUp()
    {
        $this->builder = new DoctrineDBALRotatorBuilder(
            $this->container = new Container()
        );
    }

    public function testConfigureOptionsResolver()
    {
        $this->builder->configureOptionsResolver(
            \Mockery::mock('Symfony\Component\OptionsResolver\OptionsResolver')
                ->shouldReceive('setRequired')
                ->once()
                ->with(array(
                    'connection',
                    'table',
                    'fields',
                    'id_field'
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with(array(
                    'connection' => 'Doctrine\DBAL\Connection',
                    'table' => 'string',
                    'fields' => 'array',
                    'id_field' => 'string'
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addAllowedTypes')
                ->once()
                ->with(array(
                    'connection' => 'string',
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('setNormalizers')
                ->once()
                ->with(new EqualsMatcher(array(
                    'connection' => function () {},
                )))
                ->andReturn(\Mockery::self())
                ->getMock()
        );
        $connection = \Mockery::mock('Doctrine\DBAL\Connection');
        $resolver = new OptionsResolver();
        $this->builder->configureOptionsResolver($resolver);
        $options = $resolver->resolve(array('connection' => $connection, 'table' => '', 'fields' => array(), 'id_field' => ''));
        $this->assertSame($connection, $options['connection']);
        $this->container->set('connection', $connection);
        $options = $resolver->resolve(array('connection' => 'connection', 'table' => '', 'fields' => array(), 'id_field' => ''));
        $this->assertSame($connection, $options['connection']);
    }
}
