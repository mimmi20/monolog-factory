<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Client;

use Elasticsearch\Client as V7Client;
use Elasticsearch\Common\Exceptions\AuthenticationConfigException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Client\ElasticsearchV7Factory;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function class_exists;

#[IgnoreDeprecations]
final class ElasticsearchV7FactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Hosts provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigWithWrongHostConfig(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Host data provided');

        $factory($container, '', ['hosts' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigWithConfig(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', ['host' => 42], ['host' => 'localhost.test']], 'api-id' => 'test-id', 'api-key' => 'api-key']);

        self::assertInstanceOf(V7Client::class, $client);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigWithConfig2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', ['host' => 42], ['port' => '4711'], ['host' => 'localhost.test']], 'retries' => 2, 'username' => 'user', 'password' => 'pass', 'metadata' => false]);

        self::assertInstanceOf(V7Client::class, $client);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws AuthenticationConfigException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigWithConfig3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV7Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', ['host' => 42], ['port' => '4711'], ['host' => 'localhost.test']], 'retries' => 2, 'api-id' => 'test-id', 'api-key' => 'api-key', 'metadata' => false]);

        self::assertInstanceOf(V7Client::class, $client);
    }
}
