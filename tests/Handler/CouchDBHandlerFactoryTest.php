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

namespace Mimmi20Test\MonologFactory\Handler;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\CouchDBHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\CouchDBHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class CouchDBHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');

        $options = $optionsP->getValue($handler);

        self::assertSame('localhost', $options['host']);
        self::assertSame(5984, $options['port']);
        self::assertSame('logger', $options['dbname']);
        self::assertNull($options['username']);
        self::assertNull($options['password']);

        self::assertInstanceOf(JsonFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');

        $options = $optionsP->getValue($handler);

        self::assertSame('localhost', $options['host']);
        self::assertSame(5984, $options['port']);
        self::assertSame('logger', $options['dbname']);
        self::assertNull($options['username']);
        self::assertNull($options['password']);

        self::assertInstanceOf(JsonFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig(): void
    {
        $level    = LogLevel::ERROR;
        $host     = 'testhost';
        $port     = 42;
        $dbname   = 'test';
        $userName = 'test-user';
        $password = 'test-password';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password]);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Level::Error, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');

        $options = $optionsP->getValue($handler);

        self::assertSame($host, $options['host']);
        self::assertSame($port, $options['port']);
        self::assertSame($dbname, $options['dbname']);
        self::assertSame($userName, $options['username']);
        self::assertSame($password, $options['password']);

        self::assertInstanceOf(JsonFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $level     = LogLevel::ERROR;
        $host      = 'testhost';
        $port      = 42;
        $dbname    = 'test';
        $userName  = 'test-user';
        $password  = 'test-password';
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CouchDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $level     = LogLevel::ERROR;
        $host      = 'testhost';
        $port      = 42;
        $dbname    = 'test';
        $userName  = 'test-user';
        $password  = 'test-password';
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new CouchDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $level     = LogLevel::ERROR;
        $host      = 'testhost';
        $port      = 42;
        $dbname    = 'test';
        $userName  = 'test-user';
        $password  = 'test-password';
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'formatter' => $formatter]);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Level::Error, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');

        $options = $optionsP->getValue($handler);

        self::assertSame($host, $options['host']);
        self::assertSame($port, $options['port']);
        self::assertSame($dbname, $options['dbname']);
        self::assertSame($userName, $options['username']);
        self::assertSame($password, $options['password']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $level     = LogLevel::ERROR;
        $host      = 'testhost';
        $port      = 42;
        $dbname    = 'test';
        $userName  = 'test-user';
        $password  = 'test-password';
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(null);

        $factory = new CouchDBHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $level      = LogLevel::ERROR;
        $host       = 'testhost';
        $port       = 42;
        $dbname     = 'test';
        $userName   = 'test-user';
        $password   = 'test-password';
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CouchDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $level      = LogLevel::ERROR;
        $host       = 'testhost';
        $port       = 42;
        $dbname     = 'test';
        $userName   = 'test-user';
        $password   = 'test-password';
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new CouchDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $level      = LogLevel::ERROR;
        $host       = 'testhost';
        $port       = 42;
        $dbname     = 'test';
        $userName   = 'test-user';
        $password   = 'test-password';
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

        $processor1 = $this->getMockBuilder(GitProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor2 = $this->getMockBuilder(HostnameProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['abc', [], $processor1],
                    ['xyz', ['efg' => 'ijk'], $processor2],
                ],
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'processors' => $processors]);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Level::Error, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');

        $options = $optionsP->getValue($handler);

        self::assertSame($host, $options['host']);
        self::assertSame($port, $options['port']);
        self::assertSame($dbname, $options['dbname']);
        self::assertSame($userName, $options['username']);
        self::assertSame($password, $options['password']);

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $level      = LogLevel::ERROR;
        $host       = 'testhost';
        $port       = 42;
        $dbname     = 'test';
        $userName   = 'test-user';
        $password   = 'test-password';
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new CouchDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $level      = LogLevel::ERROR;
        $host       = 'testhost';
        $port       = 42;
        $dbname     = 'test';
        $userName   = 'test-user';
        $password   = 'test-password';
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new CouchDBHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password, 'processors' => $processors]);
    }
}
