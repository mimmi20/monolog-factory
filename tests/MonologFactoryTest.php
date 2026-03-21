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

namespace Mimmi20Test\MonologFactory;

use DateTimeZone;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\MonologFactory;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class MonologFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutArrayOptions(): void
    {
        $requestedName = Logger::class;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must be an Array');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyArrayOptions(): void
    {
        $requestedName = Logger::class;
        $options       = [];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('The name for the monolog logger is missing');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithTimezone(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => 'Mars/One'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(DateTimeZone::class, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithTimezone2(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => true];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(DateTimeZone::class, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithTimezone3(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => 'Europe/Berlin'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(DateTimeZone::class, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithTimezone4(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlers(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => 'fake'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlers2(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => [[]]];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlers3(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => [['enabled' => true]]];

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::never())
            ->method('build');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithHandlers4(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'handlers' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'options' => ['abc' => 'def'],
                    'type' => 'xyz',
                ],
                ['type' => 'abc'],
            ],
            'name' => 'xyz',
            'timezone' => $timezone,
        ];

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $monologHandlerPluginManager->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        default => self::assertSame('abc', $name, (string) $invocation),
                        1 => self::assertSame('xyz', $name, (string) $invocation),
                    };

                    self::assertSame([], $options, (string) $invocation);

                    throw new ServiceNotFoundException();
                },
            )
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithHandlers5(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'handlers' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'options' => ['abc' => 'def'],
                    'type' => 'xyz',
                ],
                ['type' => 'abc'],
                $this->createMock(HandlerInterface::class),
            ],
            'name' => 'xyz',
            'timezone' => $timezone,
        ];

        $handler = $this->createMock(HandlerInterface::class);

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['xyz', ['abc' => 'def'], $handler],
                    ['abc', [], $handler],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getHandlers());
        self::assertCount(4, $logger->getHandlers());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithHandlers6(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'handlers' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'options' => ['abc' => 'def'],
                    'type' => 'xyz',
                ],
                'xyz',
                $this->createMock(HandlerInterface::class),
            ],
            'name' => 'xyz',
            'timezone' => $timezone,
        ];

        $handler = $this->createMock(HandlerInterface::class);

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with('xyz', ['abc' => 'def'])
            ->willReturn($handler);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getHandlers());
        self::assertCount(3, $logger->getHandlers());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithProcessors(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => 'fake'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithProcessors2(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => [[]]];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithProcessors3(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => [['enabled' => true]]];

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
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithProcessors4(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'type' => 'xyz',
                ],
                ['type' => 'abc'],
            ],
            'timezone' => $timezone,
        ];

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $monologProcessorPluginManager->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame('abc', $name, (string) $invocation),
                        default => self::assertSame('xyz', $name, (string) $invocation),
                    };

                    self::assertSame([], $options, (string) $invocation);

                    throw new ServiceNotFoundException();
                },
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithProcessors5(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'options' => ['efg' => 'ijk'],
                    'type' => 'xyz',
                ],
                ['type' => 'abc'],
                static fn (LogRecord $record): LogRecord => $record,
            ],
            'timezone' => $timezone,
        ];

        $processor = $this->createMock(ProcessorInterface::class);

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['abc', [], $processor],
                    ['xyz', ['efg' => 'ijk'], $processor],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithProcessors7(): void
    {
        $requestedName = Logger::class;
        $timezone      = 'Europe/London';
        $options       = [
            'name' => 'xyz',
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'options' => ['efg' => 'ijk'],
                    'type' => 'xyz',
                ],
                ['type' => 'abc'],
                static fn (LogRecord $record): LogRecord => $record,
            ],
            'timezone' => $timezone,
        ];

        $processor = $this->createMock(ProcessorInterface::class);

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['abc', [], $processor],
                    ['xyz', ['efg' => 'ijk'], $processor],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone()->getName());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
    }
}
