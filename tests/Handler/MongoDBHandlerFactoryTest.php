<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Handler;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\MongoDBHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Manager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\MongoDBFormatter;
use Monolog\Handler\MongoDBHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function sprintf;

final class MongoDBHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $mongoDBHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $mongoDBHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $client = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No database provided');

        $mongoDBHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $client   = true;
        $database = 'test-database';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No collection provided');

        $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig3(): void
    {
        $client     = true;
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig4(): void
    {
        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', MongoDBHandler::class),
        );

        $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig5(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->createStub(Collection::class);

        $clientClass = $this->createMock(Client::class);
        $clientClass->expects(self::never())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Debug, $mongoDBHandler->getLevel());
        self::assertTrue($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig6(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->createStub(Collection::class);

        $client = $this->createMock(Client::class);
        $client->expects(self::never())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Debug, $mongoDBHandler->getLevel());
        self::assertTrue($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig7(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $client     = 'test-client';
        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($manager);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Debug, $mongoDBHandler->getLevel());
        self::assertTrue($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig8(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Debug, $mongoDBHandler->getLevel());
        self::assertTrue($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig9(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->createStub(Collection::class);

        $clientClass = $this->createMock(Client::class);
        $clientClass->expects(self::never())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig10(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->createStub(Collection::class);

        $client = $this->createMock(Client::class);
        $client->expects(self::never())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig11(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client     = 'test-client';
        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($manager);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig12(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig13(): void
    {
        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(value: true);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', MongoDBHandler::class));

        $mongoDBHandlerFactory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $monologFormatterPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        self::assertSame($formatter, $mongoDBHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            static fn (array $record): array => $record,
        ];

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $processor1 = $this->createStub(GitProcessor::class);

        $processor2 = $this->createStub(HostnameProcessor::class);

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['abc', [], $processor1],
                    ['xyz', ['efg' => 'ijk'], $processor2],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $mongoDBHandler = $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);

        self::assertInstanceOf(MongoDBHandler::class, $mongoDBHandler);

        self::assertSame(Level::Alert, $mongoDBHandler->getLevel());
        self::assertFalse($mongoDBHandler->getBubble());

        $reflectionProperty = new ReflectionProperty($mongoDBHandler, 'processors');

        $processors = $reflectionProperty->getValue($mongoDBHandler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $manager    = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(value: null);

        $mongoDBHandlerFactory = new MongoDBHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $mongoDBHandlerFactory($container, '', ['client' => $manager, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
