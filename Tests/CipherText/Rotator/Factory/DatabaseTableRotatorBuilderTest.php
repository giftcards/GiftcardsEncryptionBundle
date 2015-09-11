<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 9/11/15
 * Time: 12:20 PM
 */

namespace Giftcards\EncryptionBundle\Tests\CipherText\Rotator\Factory;

use Giftcards\Encryption\Tests\CipherText\Rotator\Factory\DatabaseTableRotatorBuilderTest as BaseDatabaseTableRotatorBuilderTest;
use Giftcards\Encryption\Tests\Mock\Mockery\Matcher\EqualsMatcher;
use Giftcards\Encryption\Tests\MockPDO;
use Giftcards\EncryptionBundle\CipherText\Rotator\Factory\DatabaseTableRotatorBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @property DatabaseTableRotatorBuilder $builder
 */
class DatabaseTableRotatorBuilderTest extends BaseDatabaseTableRotatorBuilderTest
{
    /** @var  ContainerInterface */
    protected $container;

    public function setUp()
    {
        $this->builder = new DatabaseTableRotatorBuilder(
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
                    'pdo',
                    'table',
                    'fields',
                    'id_field'
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with(array(
                    'pdo' => 'PDO',
                    'table' => 'string',
                    'fields' => 'array',
                    'id_field' => 'string'
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('addAllowedTypes')
                ->once()
                ->with(array(
                    'pdo' => 'string',
                ))
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('setNormalizers')
                ->once()
                ->with(new EqualsMatcher(array(
                    'pdo' => function () {},
                )))
                ->andReturn(\Mockery::self())
                ->getMock()
        );
        $pdo = new MockPDO(\Mockery::mock());
        $resolver = new OptionsResolver();
        $this->builder->configureOptionsResolver($resolver);
        $options = $resolver->resolve(array('pdo' => $pdo, 'table' => '', 'fields' => array(), 'id_field' => ''));
        $this->assertSame($pdo, $options['pdo']);
        $this->container->set('pdo', $pdo);
        $options = $resolver->resolve(array('pdo' => 'pdo', 'table' => '', 'fields' => array(), 'id_field' => ''));
        $this->assertSame($pdo, $options['pdo']);
    }
}
