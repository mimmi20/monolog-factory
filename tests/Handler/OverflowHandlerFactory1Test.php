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
use Mimmi20\MonologFactory\Handler\OverflowHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\OverflowHandler;
use Monolog\Level;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class OverflowHandlerFactory1Test extends TestCase
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

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $overflowHandlerFactory($container, '');
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

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $overflowHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $overflowHandlerFactory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $overflowHandlerFactory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithLoaderError(): void
    {
        $type = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotCreatedException());

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithLoaderError2(): void
    {
        $type = 'abc';

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 0,
            Level::Critical->value => 0,
            Level::Debug->value => 0,
            Level::Emergency->value => 0,
            Level::Error->value => 0,
            Level::Info->value => 0,
            Level::Notice->value => 0,
            Level::Warning->value => 0,
        ];
        $formatterClass       = $this->createStub(LineFormatter::class);

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $overflowHandler = $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(OverflowHandler::class, $overflowHandler);

        self::assertSame(Level::Debug, $overflowHandler->getLevel());
        self::assertTrue($overflowHandler->getBubble());

        $handlerP = new ReflectionProperty($overflowHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($overflowHandler));

        $thm = new ReflectionProperty($overflowHandler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($overflowHandler));

        self::assertSame($formatterClass, $overflowHandler->getFormatter());
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig2(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $overflowHandler = $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(OverflowHandler::class, $overflowHandler);

        self::assertSame(Level::Alert, $overflowHandler->getLevel());
        self::assertFalse($overflowHandler->getBubble());

        $handlerP = new ReflectionProperty($overflowHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($overflowHandler));

        $thm = new ReflectionProperty($overflowHandler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($overflowHandler));

        self::assertSame($formatterClass, $overflowHandler->getFormatter());
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
        $type      = 'abc';
        $formatter = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type      = 'abc';
        $formatter = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
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
        $type      = 'abc';
        $formatter = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $monologHandlerPluginManager): AbstractPluginManager {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            MonologFormatterPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $monologHandlerPluginManager,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologFormatterPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $overflowHandler = $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatterClass]);

        self::assertInstanceOf(OverflowHandler::class, $overflowHandler);

        self::assertSame(Level::Alert, $overflowHandler->getLevel());
        self::assertFalse($overflowHandler->getBubble());

        $handlerP = new ReflectionProperty($overflowHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($overflowHandler));

        $thm = new ReflectionProperty($overflowHandler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($overflowHandler));

        self::assertSame($formatterClass, $overflowHandler->getFormatter());
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
        $type      = 'abc';
        $formatter = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $monologHandlerPluginManager): AbstractPluginManager {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            MonologFormatterPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $monologHandlerPluginManager,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
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
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologFormatterPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatterClass])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $overflowHandler = $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatterClass]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(OverflowHandler::class, $overflowHandler);

        self::assertSame(Level::Alert, $overflowHandler->getLevel());
        self::assertFalse($overflowHandler->getBubble());

        $handlerP = new ReflectionProperty($overflowHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($overflowHandler));

        $thm = new ReflectionProperty($overflowHandler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($overflowHandler));

        self::assertSame($formatterClass, $overflowHandler->getFormatter());
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
        $type           = 'abc';
        $formatterClass = $this->createStub(LineFormatter::class);

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatterClass])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatterClass]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $processors           = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $overflowHandler = $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(OverflowHandler::class, $overflowHandler);

        self::assertSame(Level::Alert, $overflowHandler->getLevel());
        self::assertFalse($overflowHandler->getBubble());

        $handlerP = new ReflectionProperty($overflowHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($overflowHandler));

        $thm = new ReflectionProperty($overflowHandler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($overflowHandler));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type       = 'abc';
        $processors = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['processors' => $processors])
            ->willReturn($handler2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $overflowHandlerFactory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $overflowHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
