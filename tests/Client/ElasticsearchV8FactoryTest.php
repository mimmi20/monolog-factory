<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Client;

use Elastic\Elasticsearch\Client as V8Client;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Client\ElasticsearchV8Factory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function class_exists;

final class ElasticsearchV8FactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvokeWithoutConfig(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV8Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
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

        $factory = new ElasticsearchV8Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Hosts provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigWithWrongClient(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV8Factory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Host data provided');

        $factory($container, '', ['hosts' => true]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigWithConfig(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV8Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', 'localhost.test'], 'api-id' => 'test-id', 'api-key' => 'api-key']);

        self::assertInstanceOf(V8Client::class, $client);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigWithConfig2(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV8Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', 'localhost.test'], 'retries' => 2, 'username' => 'user', 'password' => 'pass', 'metadata' => false]);

        self::assertInstanceOf(V8Client::class, $client);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigWithConfig3(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchV8Factory();

        $client = $factory($container, '', ['hosts' => ['localhost', 'localhost.test'], 'retries' => 2, 'api-id' => 'test-id', 'api-key' => 'api-key', 'metadata' => false]);

        self::assertInstanceOf(V8Client::class, $client);
    }
}
