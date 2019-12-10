<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/11/15
 * Time: 12:20 PM
 */

namespace Giftcards\EncryptionBundle\Tests\CipherText\Rotator\Factory;

use Giftcards\Encryption\Tests\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilderTest as BaseDoctrineDBALRotatorBuilderTest;
use Giftcards\Encryption\Tests\Mock\Mockery\Matcher\EqualsMatcher;
use Giftcards\EncryptionBundle\CipherText\Rotator\Factory\DoctrineDBALRotatorBuilder;
use Mockery;
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

    public function setUp() : void
    {
        $this->builder = new DoctrineDBALRotatorBuilder(
            $this->container = new Container()
        );
    }

    public function testConfigureOptionsResolver()
    {
        $this->builder->configureOptionsResolver(
            Mockery::mock('Symfony\Component\OptionsResolver\OptionsResolver')
                ->shouldReceive('setRequired')
                ->once()
                ->with([
                    'connection',
                    'table',
                    'fields',
                    'id_field'
                ])
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with('connection', 'Doctrine\DBAL\Connection')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with('table', 'string')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with('fields', 'array')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with('id_field', 'string')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('addAllowedTypes')
                ->once()
                ->with('connection', 'string')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setNormalizer')
                ->once()
                ->with('connection', new EqualsMatcher(function () {}))
                ->andReturnSelf()
                ->getMock()
        );
        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $resolver = new OptionsResolver();
        $this->builder->configureOptionsResolver($resolver);
        $options = $resolver->resolve(['connection' => $connection, 'table' => '', 'fields' => [], 'id_field' => '']);
        $this->assertSame($connection, $options['connection']);
        $this->container->set('connection', $connection);
        $options = $resolver->resolve(['connection' => 'connection', 'table' => '', 'fields' => [], 'id_field' => '']);
        $this->assertSame($connection, $options['connection']);
    }
}
