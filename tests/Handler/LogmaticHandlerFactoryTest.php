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

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\LogmaticHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Handler\LogmaticHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function extension_loaded;
use function sprintf;

final class LogmaticHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @requires extension openssl
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

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
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

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfig(): void
    {
        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://api.logmatic.io:10515/v1/', $handler->getConnectionString());
        self::assertSame(0.0, $handler->getTimeout());
        self::assertSame(10.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');

        self::assertSame('', $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame('', $an->getValue($handler));

        self::assertInstanceOf(LogmaticFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfig2(): void
    {
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');

        self::assertSame($hostname, $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame($appname, $an->getValue($handler));

        self::assertInstanceOf(LogmaticFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', LogmaticHandler::class),
        );

        $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;
        $formatter    = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
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
        $formatter         = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');

        self::assertSame($hostname, $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame($appname, $an->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
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
        $formatter         = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');

        self::assertSame($hostname, $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame($appname, $an->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }
}
