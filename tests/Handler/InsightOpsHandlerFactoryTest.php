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
use Mimmi20\MonologFactory\Handler\InsightOpsHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\InsightOpsHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function extension_loaded;
use function sprintf;

final class InsightOpsHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
    public function testInvokeWithConfig(): void
    {
        $token = 'test-token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $handler = $factory($container, '', ['token' => $token]);

        self::assertInstanceOf(InsightOpsHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://us.data.logs.insight.rapid7.com:443', $handler->getConnectionString());
        self::assertSame(0.0, $handler->getTimeout());
        self::assertSame(10.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        // self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    #[RequiresPhpExtension('openssl')]
    public function testInvokeWithConfig2(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
        $persistent   = true;
        $chunkSize    = 100;
        $region       = 'eu';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'region' => $region, 'useSSL' => false]);

        self::assertInstanceOf(InsightOpsHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('eu.data.logs.insight.rapid7.com:80', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithoutExtension(): void
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
        $region       = 'eu';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', InsightOpsHandler::class),
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'region' => $region]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        $token        = 'test-token';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $level        = LogLevel::ALERT;
        $bubble       = false;
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

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new InsightOpsHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(InsightOpsHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://us.data.logs.insight.rapid7.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        self::assertSame($formatter, $handler->getFormatter());

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
    #[RequiresPhpExtension('openssl')]
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
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new InsightOpsHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(InsightOpsHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://us.data.logs.insight.rapid7.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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
    #[RequiresPhpExtension('openssl')]
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
        $formatter         = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
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

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new InsightOpsHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);

        self::assertInstanceOf(InsightOpsHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://us.data.logs.insight.rapid7.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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
    #[RequiresPhpExtension('openssl')]
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('openssl')]
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithError(): void
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new InsightOpsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', InsightOpsHandler::class));

        $factory($container, '', ['token' => $token, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
    }
}
