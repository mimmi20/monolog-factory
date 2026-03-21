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

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\MonologFactory\Handler\FingersCrossedHandlerFactory;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class FingersCrossedHandlerFactory1Test extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type           = 'abc';
        $formatterClass = $this->createMock(LineFormatter::class);

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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(0, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertTrue($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertNull($ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig2(): void
    {
        $type           = 'abc';
        $formatterClass = $this->createMock(LineFormatter::class);

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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => null, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig3(): void
    {
        $type           = 'abc';
        $formatterClass = $this->createMock(LineFormatter::class);

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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => Level::Warning, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig4(): void
    {
        $type           = 'abc';
        $strategy       = $this->createMock(ChannelLevelActivationStrategy::class);
        $formatterClass = $this->createMock(LineFormatter::class);

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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategy, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig5(): void
    {
        $type           = 'abc';
        $strategy       = LogLevel::WARNING;
        $formatterClass = $this->createMock(LineFormatter::class);

        $activationStrategyPluginManager = $this->createMock(AbstractPluginManager::class);
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(false);
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');
        $activationStrategyPluginManager->expects(self::never())
            ->method('build');

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [ActivationStrategyPluginManager::class, $activationStrategyPluginManager],
                ],
            );

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig6(): void
    {
        $type           = 'abc';
        $strategy       = 'xyz';
        $strategyClass  = $this->createMock(ChannelLevelActivationStrategy::class);
        $formatterClass = $this->createMock(LineFormatter::class);

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $activationStrategyPluginManager = $this->createMock(AbstractPluginManager::class);
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willReturn($strategyClass);
        $activationStrategyPluginManager->expects(self::never())
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
                    [ActivationStrategyPluginManager::class, $activationStrategyPluginManager],
                ],
            );

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig7(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

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
        $matcher = self::exactly(2);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $monologHandlerPluginManager) {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            ActivationStrategyPluginManager::class,
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load service %s', ActivationStrategyPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig8(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->createMock(AbstractPluginManager::class);
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willThrowException(new ServiceNotFoundException());

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
                    [ActivationStrategyPluginManager::class, $activationStrategyPluginManager],
                ],
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ActivationStrategy class');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig9(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->createMock(ChannelLevelActivationStrategy::class);
        $formatterClass  = $this->createMock(LineFormatter::class);

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $activationStrategyPluginManager = $this->createMock(AbstractPluginManager::class);
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');
        $activationStrategyPluginManager->expects(self::once())
            ->method('build')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
                    [ActivationStrategyPluginManager::class, $activationStrategyPluginManager],
                ],
            );

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Level::Warning, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \PHPUnit\Event\NoPreviousThrowableException
     */
    public function testInvokeWithHandlerConfig10(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->createMock(AbstractPluginManager::class);
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');
        $activationStrategyPluginManager->expects(self::once())
            ->method('build')
            ->with($strategyName, $strategyOptions)
            ->willThrowException(new ServiceNotFoundException());

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
                    [ActivationStrategyPluginManager::class, $activationStrategyPluginManager],
                ],
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ActivationStrategy class');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }
}
