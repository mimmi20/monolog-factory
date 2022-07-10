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

namespace Mimmi20Test\MonologFactory\Handler;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\SocketHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class SocketHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new SocketHandlerFactory();

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

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No connectionString provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $connectionString = 'conn-string';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame(0.0, $handler->getTimeout());
        self::assertSame(10.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writingTimeout, $handler->getWritingTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
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
        $formatter        = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writingTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writingTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writingTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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
        $formatter  = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writingTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SocketHandler::class));

        $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writingTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }
}
