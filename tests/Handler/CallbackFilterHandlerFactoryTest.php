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
use Mimmi20\Monolog\Handler\CallbackFilterHandler;
use Mimmi20\MonologFactory\Handler\CallbackFilterHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

use function sprintf;

final class CallbackFilterHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $callbackFilterHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $callbackFilterHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $callbackFilterHandlerFactory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $callbackFilterHandlerFactory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type = 'abc';

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Debug, $callbackFilterHandler->getLevel());
        self::assertTrue($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertTrue($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig2(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter = static fn (LogRecord $logRecord, Level $level): bool => true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => $filter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig3(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $formatter = true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'formatter' => $formatter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $formatter = true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $formatter = $this->createStub(LineFormatter::class);

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'formatter' => $formatter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $formatter = $this->createStub(LineFormatter::class);

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::never())
            ->method('getFormatter');

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
            ->with($type, ['formatter' => $formatter])
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $formatter = $this->createStub(LineFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $processors = true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

        $processors = true;

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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::never())
            ->method('build');

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
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $monologHandlerPluginManager): MockObject {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            MonologProcessorPluginManager::class,
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

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, null],
                ],
            );

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors6(): void
    {
        $type = 'abc';

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter1 = static fn (LogRecord $logRecord, Level $level): bool => false;

        /**
         * @param LogRecord $record
         * @param Level $level
         *
         * @return bool
         *
         * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
         */
        $filter2 = static fn (LogRecord $logRecord, Level $level): bool => true;

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $callbackFilterHandlerFactory = new CallbackFilterHandlerFactory();

        $callbackFilterHandler = $callbackFilterHandlerFactory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'processors' => $processors]);

        self::assertInstanceOf(CallbackFilterHandler::class, $callbackFilterHandler);

        self::assertSame(Level::Alert, $callbackFilterHandler->getLevel());
        self::assertFalse($callbackFilterHandler->getBubble());

        $handlerP = new ReflectionProperty($callbackFilterHandler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($callbackFilterHandler));

        $bb = new ReflectionProperty($callbackFilterHandler, 'bubble');

        self::assertFalse($bb->getValue($callbackFilterHandler));

        $filtersP = new ReflectionProperty($callbackFilterHandler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($callbackFilterHandler));

        $proc = new ReflectionProperty($callbackFilterHandler, 'processors');

        $processors = $proc->getValue($callbackFilterHandler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }
}
