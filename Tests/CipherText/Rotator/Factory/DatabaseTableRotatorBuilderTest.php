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
use Mockery;
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

    public function setUp() : void
    {
        $this->builder = new DatabaseTableRotatorBuilder(
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
                    'pdo',
                    'table',
                    'fields',
                    'id_field'
                ])
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setAllowedTypes')
                ->once()
                ->with('pdo', 'PDO')
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
                ->with('pdo', 'string')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('setNormalizer')
                ->once()
                ->with('pdo', new EqualsMatcher(function () {}))
                ->andReturnSelf()
                ->getMock()
        );
        $pdo = new MockPDO(Mockery::mock());
        $resolver = new OptionsResolver();
        $this->builder->configureOptionsResolver($resolver);
        $options = $resolver->resolve(['pdo' => $pdo, 'table' => '', 'fields' => [], 'id_field' => '']);
        $this->assertSame($pdo, $options['pdo']);
        $this->container->set('pdo', $pdo);
        $options = $resolver->resolve(['pdo' => 'pdo', 'table' => '', 'fields' => [], 'id_field' => '']);
        $this->assertSame($pdo, $options['pdo']);
    }
}
