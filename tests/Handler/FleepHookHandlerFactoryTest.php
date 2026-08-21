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
use Mimmi20\MonologFactory\Handler\FleepHookHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FleepHookHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function extension_loaded;
use function sprintf;

final class FleepHookHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $fleepHookHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $fleepHookHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeConfig(): void
    {
        $token = 'test-token';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $fleepHookHandler = $fleepHookHandlerFactory($container, '', ['token' => $token]);

        self::assertInstanceOf(FleepHookHandler::class, $fleepHookHandler);

        self::assertSame(Level::Debug, $fleepHookHandler->getLevel());
        self::assertTrue($fleepHookHandler->getBubble());
        self::assertSame('ssl://fleep.io:443', $fleepHookHandler->getConnectionString());
        self::assertSame(0.0, $fleepHookHandler->getTimeout());
        self::assertSame(10.0, $fleepHookHandler->getWritingTimeout());
        self::assertSame(60.0, $fleepHookHandler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($fleepHookHandler->isPersistent());

        $at = new ReflectionProperty($fleepHookHandler, 'token');

        self::assertSame($token, $at->getValue($fleepHookHandler));

        self::assertInstanceOf(LineFormatter::class, $fleepHookHandler->getFormatter());

        $proc = new ReflectionProperty($fleepHookHandler, 'processors');

        $processors = $proc->getValue($fleepHookHandler);

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
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeConfig2(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $fleepHookHandler = $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(FleepHookHandler::class, $fleepHookHandler);

        self::assertSame(Level::Alert, $fleepHookHandler->getLevel());
        self::assertFalse($fleepHookHandler->getBubble());
        self::assertSame('ssl://fleep.io:443', $fleepHookHandler->getConnectionString());
        self::assertSame($timeout, $fleepHookHandler->getTimeout());
        self::assertSame($writeTimeout, $fleepHookHandler->getWritingTimeout());
        self::assertSame(60.0, $fleepHookHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $fleepHookHandler->getChunkSize());
        self::assertTrue($fleepHookHandler->isPersistent());

        $at = new ReflectionProperty($fleepHookHandler, 'token');

        self::assertSame($token, $at->getValue($fleepHookHandler));

        self::assertInstanceOf(LineFormatter::class, $fleepHookHandler->getFormatter());

        $proc = new ReflectionProperty($fleepHookHandler, 'processors');

        $processors = $proc->getValue($fleepHookHandler);

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
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $formatter    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $formatter    = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $token             = 'test-token';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $fleepHookHandler = $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(FleepHookHandler::class, $fleepHookHandler);

        self::assertSame(Level::Alert, $fleepHookHandler->getLevel());
        self::assertFalse($fleepHookHandler->getBubble());
        self::assertSame('ssl://fleep.io:443', $fleepHookHandler->getConnectionString());
        self::assertSame($timeout, $fleepHookHandler->getTimeout());
        self::assertSame($writeTimeout, $fleepHookHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $fleepHookHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $fleepHookHandler->getChunkSize());
        self::assertTrue($fleepHookHandler->isPersistent());

        $at = new ReflectionProperty($fleepHookHandler, 'token');

        self::assertSame($token, $at->getValue($fleepHookHandler));

        self::assertSame($formatter, $fleepHookHandler->getFormatter());

        $proc = new ReflectionProperty($fleepHookHandler, 'processors');

        $processors = $proc->getValue($fleepHookHandler);

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
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $token             = 'test-token';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $fleepHookHandler = $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(FleepHookHandler::class, $fleepHookHandler);

        self::assertSame(Level::Alert, $fleepHookHandler->getLevel());
        self::assertFalse($fleepHookHandler->getBubble());
        self::assertSame('ssl://fleep.io:443', $fleepHookHandler->getConnectionString());
        self::assertSame($timeout, $fleepHookHandler->getTimeout());
        self::assertSame($writeTimeout, $fleepHookHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $fleepHookHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $fleepHookHandler->getChunkSize());
        self::assertTrue($fleepHookHandler->isPersistent());

        $at = new ReflectionProperty($fleepHookHandler, 'token');

        self::assertSame($token, $at->getValue($fleepHookHandler));

        self::assertSame($formatter, $fleepHookHandler->getFormatter());

        $proc = new ReflectionProperty($fleepHookHandler, 'processors');

        $processors = $proc->getValue($fleepHookHandler);

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
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $token             = 'test-token';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
        $level             = LogLevel::ALERT;
        $bubble            = false;
        $persistent        = true;
        $chunkSize         = 100;
        $formatter         = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $processors   = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $processors   = [
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $token             = 'test-token';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $fleepHookHandler = $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);

        self::assertInstanceOf(FleepHookHandler::class, $fleepHookHandler);

        self::assertSame(Level::Alert, $fleepHookHandler->getLevel());
        self::assertFalse($fleepHookHandler->getBubble());
        self::assertSame('ssl://fleep.io:443', $fleepHookHandler->getConnectionString());
        self::assertSame($timeout, $fleepHookHandler->getTimeout());
        self::assertSame($writeTimeout, $fleepHookHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $fleepHookHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $fleepHookHandler->getChunkSize());
        self::assertTrue($fleepHookHandler->isPersistent());

        $at = new ReflectionProperty($fleepHookHandler, 'token');

        self::assertSame($token, $at->getValue($fleepHookHandler));

        $proc = new ReflectionProperty($fleepHookHandler, 'processors');

        $processors = $proc->getValue($fleepHookHandler);

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
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $processor3   = static fn (array $record): array => $record;
        $processors   = [
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $processor3   = static fn (array $record): array => $record;
        $processors   = [
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

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutException(): void
    {
        if (extension_loaded('openssl')) {
            self::markTestSkipped('This test checks the exception if the openssl extension is missing');
        }

        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $fleepHookHandlerFactory = new FleepHookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', FleepHookHandler::class));

        $fleepHookHandlerFactory($container, '', ['token' => $token, 'level' => $level, 'bubble' => $bubble, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
    }
}
