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
use Mimmi20\MonologFactory\Handler\SocketHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SocketHandler;
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

use function sprintf;

final class SocketHandlerFactoryTest extends TestCase
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

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $socketHandlerFactory($container, '');
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

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No connectionString provided');

        $socketHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $connectionString = 'conn-string';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Debug, $socketHandler->getLevel());
        self::assertTrue($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame(0.0, $socketHandler->getTimeout());
        self::assertSame(10.0, $socketHandler->getWritingTimeout());
        self::assertSame(60.0, $socketHandler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($socketHandler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $socketHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
    public function testInvokeWithConfig2(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Alert, $socketHandler->getLevel());
        self::assertFalse($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame($timeout, $socketHandler->getTimeout());
        self::assertSame($writingTimeout, $socketHandler->getWritingTimeout());
        self::assertSame($chunkSize, $socketHandler->getChunkSize());
        self::assertTrue($socketHandler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $socketHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $formatter        = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $formatter        = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $connectionString  = 'conn-string';
        $timeout           = 42.0;
        $writingTimeout    = 120.0;
        $connectionTimeout = 72.0;
        $level             = LogLevel::ALERT;
        $bubble            = false;
        $persistent        = true;
        $chunkSize         = 100;
        $formatter         = $this->createStub(LineFormatter::class);

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

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Alert, $socketHandler->getLevel());
        self::assertFalse($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame($timeout, $socketHandler->getTimeout());
        self::assertSame($writingTimeout, $socketHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $socketHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $socketHandler->getChunkSize());
        self::assertTrue($socketHandler->isPersistent());

        self::assertSame($formatter, $socketHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $connectionString  = 'conn-string';
        $timeout           = 42.0;
        $writingTimeout    = 120.0;
        $connectionTimeout = 72.0;
        $level             = LogLevel::ALERT;
        $bubble            = false;
        $persistent        = true;
        $chunkSize         = 100;
        $formatter         = $this->createStub(LineFormatter::class);

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

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writingTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Alert, $socketHandler->getLevel());
        self::assertFalse($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame($timeout, $socketHandler->getTimeout());
        self::assertSame($writingTimeout, $socketHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $socketHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $socketHandler->getChunkSize());
        self::assertTrue($socketHandler->isPersistent());

        self::assertSame($formatter, $socketHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $connectionString = 'conn-string';

        $timeout           = 42.0;
        $writingTimeout    = 120.0;
        $connectionTimeout = 72.0;

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $persistent = true;
        $chunkSize  = 100;
        $formatter  = $this->createStub(LineFormatter::class);

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

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Alert, $socketHandler->getLevel());
        self::assertFalse($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame($timeout, $socketHandler->getTimeout());
        self::assertSame($writingTimeout, $socketHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $socketHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $socketHandler->getChunkSize());
        self::assertTrue($socketHandler->isPersistent());

        self::assertSame($formatter, $socketHandler->getFormatter());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
    public function testInvokeWithConfigAndFormatter5(): void
    {
        $connectionString = 'conn-string';

        $timeout           = 42.0;
        $writingTimeout    = 120.0;
        $connectionTimeout = 72.0;

        $level      = LogLevel::ALERT;
        $bubble     = false;
        $persistent = true;
        $chunkSize  = 100;
        $formatter  = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $processors       = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $processors       = [
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

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $connectionString  = 'conn-string';
        $timeout           = 42.0;
        $writingTimeout    = 120.0;
        $connectionTimeout = 60.0;
        $level             = LogLevel::ALERT;
        $bubble            = false;
        $persistent        = true;
        $chunkSize         = 100;
        $processor3        = static fn (array $record): array => $record;
        $processors        = [
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

        $socketHandlerFactory = new SocketHandlerFactory();

        $socketHandler = $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);

        self::assertInstanceOf(SocketHandler::class, $socketHandler);

        self::assertSame(Level::Alert, $socketHandler->getLevel());
        self::assertFalse($socketHandler->getBubble());
        self::assertSame($connectionString, $socketHandler->getConnectionString());
        self::assertSame($timeout, $socketHandler->getTimeout());
        self::assertSame($writingTimeout, $socketHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $socketHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $socketHandler->getChunkSize());
        self::assertTrue($socketHandler->isPersistent());

        $reflectionProperty = new ReflectionProperty($socketHandler, 'processors');

        $processors = $reflectionProperty->getValue($socketHandler);

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $processor3       = static fn (array $record): array => $record;
        $processors       = [
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

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::never())
            ->method('build');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $processor3       = static fn (array $record): array => $record;
        $processors       = [
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

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(value: null);

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithNegativeTimeout(): void
    {
        $connectionString = 'conn-string';
        $timeout          = -1;
        $writingTimeout   = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;
        $processors       = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $socketHandlerFactory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SocketHandler::class));

        $socketHandlerFactory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }
}
