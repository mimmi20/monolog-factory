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
use Mimmi20\MonologFactory\Handler\RollbarHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RollbarHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use Rollbar\Config;
use Rollbar\RollbarLogger;

use function class_exists;
use function sprintf;

#[RequiresPhp(versionRequirement: '< 8.1.0')]
final class RollbarHandlerFactoryTest extends TestCase
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $rollbarHandlerFactory($container, '');
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No access token provided');

        $rollbarHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithTooShortToken(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token = 'token';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create service %s', RollbarLogger::class));

        $rollbarHandlerFactory($container, '', ['access_token' => $token]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token = 'tokentokentokentokentokentokenab';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $rollbarHandler = $rollbarHandlerFactory($container, '', ['access_token' => $token]);

        self::assertInstanceOf(RollbarHandler::class, $rollbarHandler);

        self::assertSame(Level::Debug, $rollbarHandler->getLevel());
        self::assertTrue($rollbarHandler->getBubble());

        $rollbarloggerP = new ReflectionProperty($rollbarHandler, 'rollbarLogger');

        $rollbarlogger = $rollbarloggerP->getValue($rollbarHandler);
        $this->assertInstanceOf(RollbarLogger::class, $rollbarlogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        $this->assertInstanceOf(Config::class, $rollbarConfig);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertTrue($rollbarConfig->enabled());
        self::assertTrue($rollbarConfig->transmitting());
        self::assertTrue($rollbarConfig->loggingPayload());
        self::assertSame(Config::VERBOSE_NONE, $rollbarConfig->verbose());
        self::assertSame('production', $rollbarConfig->getDataBuilder()->getEnvironment());

        self::assertInstanceOf(LineFormatter::class, $rollbarHandler->getFormatter());

        $proc = new ReflectionProperty($rollbarHandler, 'processors');

        $processors = $proc->getValue($rollbarHandler);

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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $rollbarHandler = $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level]);

        self::assertInstanceOf(RollbarHandler::class, $rollbarHandler);

        self::assertSame(Level::Error, $rollbarHandler->getLevel());
        self::assertFalse($rollbarHandler->getBubble());

        $rollbarloggerP = new ReflectionProperty($rollbarHandler, 'rollbarLogger');

        $rollbarlogger = $rollbarloggerP->getValue($rollbarHandler);
        $this->assertInstanceOf(RollbarLogger::class, $rollbarlogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        $this->assertInstanceOf(Config::class, $rollbarConfig);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertFalse($rollbarConfig->enabled());
        self::assertFalse($rollbarConfig->transmitting());
        self::assertFalse($rollbarConfig->loggingPayload());
        self::assertSame($verbose, $rollbarConfig->verbose());
        self::assertSame($environment, $rollbarConfig->getDataBuilder()->getEnvironment());

        self::assertInstanceOf(LineFormatter::class, $rollbarHandler->getFormatter());

        $proc = new ReflectionProperty($rollbarHandler, 'processors');

        $processors = $proc->getValue($rollbarHandler);

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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = $this->createStub(LineFormatter::class);

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

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $rollbarHandler = $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);

        self::assertInstanceOf(RollbarHandler::class, $rollbarHandler);

        self::assertSame(Level::Error, $rollbarHandler->getLevel());
        self::assertFalse($rollbarHandler->getBubble());

        $rollbarloggerP = new ReflectionProperty($rollbarHandler, 'rollbarLogger');

        $rollbarlogger = $rollbarloggerP->getValue($rollbarHandler);
        $this->assertInstanceOf(RollbarLogger::class, $rollbarlogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        $this->assertInstanceOf(Config::class, $rollbarConfig);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertFalse($rollbarConfig->enabled());
        self::assertFalse($rollbarConfig->transmitting());
        self::assertFalse($rollbarConfig->loggingPayload());
        self::assertSame($verbose, $rollbarConfig->verbose());
        self::assertSame($environment, $rollbarConfig->getDataBuilder()->getEnvironment());

        self::assertSame($formatter, $rollbarHandler->getFormatter());

        $proc = new ReflectionProperty($rollbarHandler, 'processors');

        $processors = $proc->getValue($rollbarHandler);

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processors  = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processors  = [
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

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processor3  = static fn (array $record): array => $record;
        $processors  = [
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

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $rollbarHandler = $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);

        self::assertInstanceOf(RollbarHandler::class, $rollbarHandler);

        self::assertSame(Level::Error, $rollbarHandler->getLevel());
        self::assertFalse($rollbarHandler->getBubble());

        $rollbarloggerP = new ReflectionProperty($rollbarHandler, 'rollbarLogger');

        $rollbarlogger = $rollbarloggerP->getValue($rollbarHandler);
        $this->assertInstanceOf(RollbarLogger::class, $rollbarlogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        $this->assertInstanceOf(Config::class, $rollbarConfig);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertFalse($rollbarConfig->enabled());
        self::assertFalse($rollbarConfig->transmitting());
        self::assertFalse($rollbarConfig->loggingPayload());
        self::assertSame($verbose, $rollbarConfig->verbose());
        self::assertSame($environment, $rollbarConfig->getDataBuilder()->getEnvironment());

        $proc = new ReflectionProperty($rollbarHandler, 'processors');

        $processors = $proc->getValue($rollbarHandler);

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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processor3  = static fn (array $record): array => $record;
        $processors  = [
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

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);
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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processor3  = static fn (array $record): array => $record;
        $processors  = [
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

        $rollbarHandlerFactory = new RollbarHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $rollbarHandlerFactory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);
    }
}
