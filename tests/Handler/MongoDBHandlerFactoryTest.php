<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022, Thomas Mueller <mimmi20@live.de>
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
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Manager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\MongoDBFormatter;
use Monolog\Handler\MongoDBHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function class_exists;
use function sprintf;

final class MongoDBHandlerFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /** @throws Exception */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', []);
    }

    /** @throws Exception */
    public function testInvokeWithConfig(): void
    {
        $client = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No database provided');

        $factory($container, '', ['client' => $client]);
    }

    /** @throws Exception */
    public function testInvokeWithConfig2(): void
    {
        $client   = true;
        $database = 'test-database';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No collection provided');

        $factory($container, '', ['client' => $client, 'database' => $database]);
    }

    /** @throws Exception */
    public function testInvokeWithConfig3(): void
    {
        $client     = true;
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /** @throws Exception */
    public function testInvokeWithConfig4(): void
    {
        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load client class for %s class', MongoDBHandler::class));

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig5(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientClass = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientClass->expects(self::once())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig6(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $database   = 'test-database';
        $collection = 'test-collection';

        $mongoCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects(self::once())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig7(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $client      = 'test-client';
        $clientClass = new Manager('mongodb://example.com:27017');
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig8(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $mongoCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientClass = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientClass->expects(self::once())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $mongoCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects(self::once())
            ->method('selectCollection')
            ->with($database, $collection)
            ->willReturn($mongoCollection);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig11(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client      = 'test-client';
        $clientClass = new Manager('mongodb://example.com:27017');
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig12(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(MongoDBFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfig13(): void
    {
        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(true);

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', MongoDBHandler::class));

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(null);

        $factory = new MongoDBHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was NULL',
        );

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
