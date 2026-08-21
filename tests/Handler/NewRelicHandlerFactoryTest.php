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
use Mimmi20\MonologFactory\Handler\NewRelicHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\NewRelicHandler;
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

final class NewRelicHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $newRelicHandler = $newRelicHandlerFactory($container, '');

        self::assertInstanceOf(NewRelicHandler::class, $newRelicHandler);

        self::assertSame(Level::Debug, $newRelicHandler->getLevel());
        self::assertTrue($newRelicHandler->getBubble());

        $an = new ReflectionProperty($newRelicHandler, 'appName');

        self::assertNull($an->getValue($newRelicHandler));

        $ea = new ReflectionProperty($newRelicHandler, 'explodeArrays');

        self::assertFalse($ea->getValue($newRelicHandler));

        $tn = new ReflectionProperty($newRelicHandler, 'transactionName');

        self::assertNull($tn->getValue($newRelicHandler));

        self::assertInstanceOf(NormalizerFormatter::class, $newRelicHandler->getFormatter());

        $proc = new ReflectionProperty($newRelicHandler, 'processors');

        $processors = $proc->getValue($newRelicHandler);

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
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $newRelicHandler = $newRelicHandlerFactory($container, '', []);

        self::assertInstanceOf(NewRelicHandler::class, $newRelicHandler);

        self::assertSame(Level::Debug, $newRelicHandler->getLevel());
        self::assertTrue($newRelicHandler->getBubble());

        $an = new ReflectionProperty($newRelicHandler, 'appName');

        self::assertNull($an->getValue($newRelicHandler));

        $ea = new ReflectionProperty($newRelicHandler, 'explodeArrays');

        self::assertFalse($ea->getValue($newRelicHandler));

        $tn = new ReflectionProperty($newRelicHandler, 'transactionName');

        self::assertNull($tn->getValue($newRelicHandler));

        self::assertInstanceOf(NormalizerFormatter::class, $newRelicHandler->getFormatter());

        $proc = new ReflectionProperty($newRelicHandler, 'processors');

        $processors = $proc->getValue($newRelicHandler);

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
    public function testInvokeWithConfig(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $newRelicHandler = $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName]);

        self::assertInstanceOf(NewRelicHandler::class, $newRelicHandler);

        self::assertSame(Level::Alert, $newRelicHandler->getLevel());
        self::assertFalse($newRelicHandler->getBubble());

        $an = new ReflectionProperty($newRelicHandler, 'appName');

        self::assertSame($appName, $an->getValue($newRelicHandler));

        $ea = new ReflectionProperty($newRelicHandler, 'explodeArrays');

        self::assertTrue($ea->getValue($newRelicHandler));

        $tn = new ReflectionProperty($newRelicHandler, 'transactionName');

        self::assertSame($transactionName, $tn->getValue($newRelicHandler));

        self::assertInstanceOf(NormalizerFormatter::class, $newRelicHandler->getFormatter());

        $proc = new ReflectionProperty($newRelicHandler, 'processors');

        $processors = $proc->getValue($newRelicHandler);

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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = $this->createStub(LineFormatter::class);

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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $newRelicHandler = $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);

        self::assertInstanceOf(NewRelicHandler::class, $newRelicHandler);

        self::assertSame(Level::Alert, $newRelicHandler->getLevel());
        self::assertFalse($newRelicHandler->getBubble());

        $an = new ReflectionProperty($newRelicHandler, 'appName');

        self::assertSame($appName, $an->getValue($newRelicHandler));

        $ea = new ReflectionProperty($newRelicHandler, 'explodeArrays');

        self::assertTrue($ea->getValue($newRelicHandler));

        $tn = new ReflectionProperty($newRelicHandler, 'transactionName');

        self::assertSame($transactionName, $tn->getValue($newRelicHandler));

        self::assertSame($formatter, $newRelicHandler->getFormatter());

        $proc = new ReflectionProperty($newRelicHandler, 'processors');

        $processors = $proc->getValue($newRelicHandler);

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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processors      = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processors      = [
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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processor3      = static fn (array $record): array => $record;
        $processors      = [
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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $newRelicHandler = $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);

        self::assertInstanceOf(NewRelicHandler::class, $newRelicHandler);

        self::assertSame(Level::Alert, $newRelicHandler->getLevel());
        self::assertFalse($newRelicHandler->getBubble());

        $an = new ReflectionProperty($newRelicHandler, 'appName');

        self::assertSame($appName, $an->getValue($newRelicHandler));

        $ea = new ReflectionProperty($newRelicHandler, 'explodeArrays');

        self::assertTrue($ea->getValue($newRelicHandler));

        $tn = new ReflectionProperty($newRelicHandler, 'transactionName');

        self::assertSame($transactionName, $tn->getValue($newRelicHandler));

        $proc = new ReflectionProperty($newRelicHandler, 'processors');

        $processors = $proc->getValue($newRelicHandler);

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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processor3      = static fn (array $record): array => $record;
        $processors      = [
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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);
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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processor3      = static fn (array $record): array => $record;
        $processors      = [
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

        $newRelicHandlerFactory = new NewRelicHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $newRelicHandlerFactory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);
    }
}
