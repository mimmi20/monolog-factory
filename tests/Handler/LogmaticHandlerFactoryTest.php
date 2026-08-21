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
use Mimmi20\MonologFactory\Handler\LogmaticHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Handler\LogmaticHandler;
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

final class LogmaticHandlerFactoryTest extends TestCase
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $logmaticHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'openssl')]
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $logmaticHandlerFactory($container, '', []);
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
    public function testInvokeWithConfig(): void
    {
        $token = 'token';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $logmaticHandler = $logmaticHandlerFactory($container, '', ['token' => $token]);

        self::assertInstanceOf(LogmaticHandler::class, $logmaticHandler);

        self::assertSame(Level::Debug, $logmaticHandler->getLevel());
        self::assertTrue($logmaticHandler->getBubble());
        self::assertSame('ssl://api.logmatic.io:10515/v1/', $logmaticHandler->getConnectionString());
        self::assertSame(0.0, $logmaticHandler->getTimeout());
        self::assertSame(10.0, $logmaticHandler->getWritingTimeout());
        self::assertSame(60.0, $logmaticHandler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($logmaticHandler->isPersistent());

        $lt = new ReflectionProperty($logmaticHandler, 'logToken');

        self::assertSame($token, $lt->getValue($logmaticHandler));

        $hn = new ReflectionProperty($logmaticHandler, 'hostname');

        self::assertSame('', $hn->getValue($logmaticHandler));

        $an = new ReflectionProperty($logmaticHandler, 'appName');

        self::assertSame('', $an->getValue($logmaticHandler));

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticHandler->getFormatter());

        $proc = new ReflectionProperty($logmaticHandler, 'processors');

        $processors = $proc->getValue($logmaticHandler);

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
    public function testInvokeWithConfig2(): void
    {
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $logmaticHandler = $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(LogmaticHandler::class, $logmaticHandler);

        self::assertSame(Level::Alert, $logmaticHandler->getLevel());
        self::assertFalse($logmaticHandler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $logmaticHandler->getConnectionString());
        self::assertSame($timeout, $logmaticHandler->getTimeout());
        self::assertSame($writeTimeout, $logmaticHandler->getWritingTimeout());
        self::assertSame(60.0, $logmaticHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $logmaticHandler->getChunkSize());
        self::assertTrue($logmaticHandler->isPersistent());

        $lt = new ReflectionProperty($logmaticHandler, 'logToken');

        self::assertSame($token, $lt->getValue($logmaticHandler));

        $hn = new ReflectionProperty($logmaticHandler, 'hostname');

        self::assertSame($hostname, $hn->getValue($logmaticHandler));

        $an = new ReflectionProperty($logmaticHandler, 'appName');

        self::assertSame($appname, $an->getValue($logmaticHandler));

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticHandler->getFormatter());

        $proc = new ReflectionProperty($logmaticHandler, 'processors');

        $processors = $proc->getValue($logmaticHandler);

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
    public function testInvokeWithoutExtension(): void
    {
        if (extension_loaded('openssl')) {
            self::markTestSkipped('This test checks the exception if the openssl extension is missing');
        }

        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', LogmaticHandler::class),
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;
        $formatter    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
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
        $token             = 'token';
        $hostname          = 'test-host';
        $appname           = 'test-app';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $logmaticHandler = $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(LogmaticHandler::class, $logmaticHandler);

        self::assertSame(Level::Alert, $logmaticHandler->getLevel());
        self::assertFalse($logmaticHandler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $logmaticHandler->getConnectionString());
        self::assertSame($timeout, $logmaticHandler->getTimeout());
        self::assertSame($writeTimeout, $logmaticHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $logmaticHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $logmaticHandler->getChunkSize());
        self::assertTrue($logmaticHandler->isPersistent());

        $lt = new ReflectionProperty($logmaticHandler, 'logToken');

        self::assertSame($token, $lt->getValue($logmaticHandler));

        $hn = new ReflectionProperty($logmaticHandler, 'hostname');

        self::assertSame($hostname, $hn->getValue($logmaticHandler));

        $an = new ReflectionProperty($logmaticHandler, 'appName');

        self::assertSame($appname, $an->getValue($logmaticHandler));

        self::assertSame($formatter, $logmaticHandler->getFormatter());

        $proc = new ReflectionProperty($logmaticHandler, 'processors');

        $processors = $proc->getValue($logmaticHandler);

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
        $token             = 'token';
        $hostname          = 'test-host';
        $appname           = 'test-app';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $logmaticHandler = $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(LogmaticHandler::class, $logmaticHandler);

        self::assertSame(Level::Alert, $logmaticHandler->getLevel());
        self::assertFalse($logmaticHandler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $logmaticHandler->getConnectionString());
        self::assertSame($timeout, $logmaticHandler->getTimeout());
        self::assertSame($writeTimeout, $logmaticHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $logmaticHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $logmaticHandler->getChunkSize());
        self::assertTrue($logmaticHandler->isPersistent());

        $lt = new ReflectionProperty($logmaticHandler, 'logToken');

        self::assertSame($token, $lt->getValue($logmaticHandler));

        $hn = new ReflectionProperty($logmaticHandler, 'hostname');

        self::assertSame($hostname, $hn->getValue($logmaticHandler));

        $an = new ReflectionProperty($logmaticHandler, 'appName');

        self::assertSame($appname, $an->getValue($logmaticHandler));

        self::assertSame($formatter, $logmaticHandler->getFormatter());

        $proc = new ReflectionProperty($logmaticHandler, 'processors');

        $processors = $proc->getValue($logmaticHandler);

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
        $token             = 'token';
        $hostname          = 'test-host';
        $appname           = 'test-app';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;
        $processors   = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
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
        $token             = 'token';
        $hostname          = 'test-host';
        $appname           = 'test-app';
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $persistent        = true;
        $chunkSize         = 100;
        $connectionTimeout = 60.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $logmaticHandler = $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);

        self::assertInstanceOf(LogmaticHandler::class, $logmaticHandler);

        self::assertSame(Level::Alert, $logmaticHandler->getLevel());
        self::assertFalse($logmaticHandler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $logmaticHandler->getConnectionString());
        self::assertSame($timeout, $logmaticHandler->getTimeout());
        self::assertSame($writeTimeout, $logmaticHandler->getWritingTimeout());
        self::assertSame($connectionTimeout, $logmaticHandler->getConnectionTimeout());
        self::assertSame($chunkSize, $logmaticHandler->getChunkSize());
        self::assertTrue($logmaticHandler->isPersistent());

        $lt = new ReflectionProperty($logmaticHandler, 'logToken');

        self::assertSame($token, $lt->getValue($logmaticHandler));

        $hn = new ReflectionProperty($logmaticHandler, 'hostname');

        self::assertSame($hostname, $hn->getValue($logmaticHandler));

        $an = new ReflectionProperty($logmaticHandler, 'appName');

        self::assertSame($appname, $an->getValue($logmaticHandler));

        $proc = new ReflectionProperty($logmaticHandler, 'processors');

        $processors = $proc->getValue($logmaticHandler);

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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
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
            ->willThrowException(new ServiceNotFoundException());

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
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
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
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

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithError(): void
    {
        if (extension_loaded('openssl')) {
            self::markTestSkipped('This test checks the exception if the openssl extension is missing');
        }

        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticHandlerFactory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', LogmaticHandler::class));

        $logmaticHandlerFactory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
    }
}
