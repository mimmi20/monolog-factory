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

use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsHandler;
use InvalidArgumentException;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\WhatFailureGroupHandlerFactory;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function extension_loaded;

final class WhatFailureGroupHandlerFactory2Test extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $handlers = [
            [
                'enabled' => false,
                'type' => FingersCrossedHandler::class,
            ],
            [
                'enabled' => true,
                'type' => FirePHPHandler::class,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

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

        $handler1 = $this->createMock(FirePHPHandler::class);
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->createMock(GelfHandler::class);
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $invokedCount = self::exactly(3);
        $monologHandlerPluginManager->expects($invokedCount)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($invokedCount, $handler1, $handler2, $handler3): HandlerInterface {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(FirePHPHandler::class, $name, (string) $invocation),
                        2 => self::assertSame(ChromePHPHandler::class, $name, (string) $invocation),
                        default => self::assertSame(GelfHandler::class, $name, (string) $invocation),
                    };

                    self::assertSame([], $options, (string) $invocation);

                    return match ($invocation) {
                        1 => $handler1,
                        2 => $handler2,
                        default => $handler3,
                    };
                },
            );

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
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $whatFailureGroupHandlerFactory = new WhatFailureGroupHandlerFactory();

        $whatFailureGroupHandler = $whatFailureGroupHandlerFactory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $whatFailureGroupHandler);

        $fp = new ReflectionProperty($whatFailureGroupHandler, 'handlers');

        $handlerClasses = $fp->getValue($whatFailureGroupHandler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($whatFailureGroupHandler, 'bubble');

        self::assertFalse($bubble->getValue($whatFailureGroupHandler));

        $proc = new ReflectionProperty($whatFailureGroupHandler, 'processors');

        $processors = $proc->getValue($whatFailureGroupHandler);

        self::assertIsArray($processors);
        self::assertCount(1, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $handlers = [
            [
                'enabled' => false,
                'type' => FingersCrossedHandler::class,
            ],
            [
                'enabled' => true,
                'type' => FirePHPHandler::class,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

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

        $handler1 = $this->createMock(FirePHPHandler::class);
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->createMock(GelfHandler::class);
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('build')
            ->willReturnMap(
                [
                    [FirePHPHandler::class, [], $handler1],
                    [ChromePHPHandler::class, [], $handler2],
                    [GelfHandler::class, [], $handler3],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $whatFailureGroupHandlerFactory = new WhatFailureGroupHandlerFactory();

        $whatFailureGroupHandler = $whatFailureGroupHandlerFactory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $whatFailureGroupHandler);

        $fp = new ReflectionProperty($whatFailureGroupHandler, 'handlers');

        $handlerClasses = $fp->getValue($whatFailureGroupHandler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($whatFailureGroupHandler, 'bubble');

        self::assertFalse($bubble->getValue($whatFailureGroupHandler));

        $proc = new ReflectionProperty($whatFailureGroupHandler, 'processors');

        $processors = $proc->getValue($whatFailureGroupHandler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $handlers = [
            [
                'enabled' => false,
                'type' => FingersCrossedHandler::class,
            ],
            [
                'enabled' => true,
                'type' => FirePHPHandler::class,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $processor1 = static fn (array $record): array => $record;
        $processor2 = static fn (array $record): array => $record;
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
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['abc', [], $processor2],
                    ['xyz', ['efg' => 'ijk'], $processor1],
                ],
            );

        $handler1 = $this->createMock(FirePHPHandler::class);
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->createMock(GelfHandler::class);
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('build')
            ->willReturnMap(
                [
                    [FirePHPHandler::class, [], $handler1],
                    [ChromePHPHandler::class, [], $handler2],
                    [GelfHandler::class, [], $handler3],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $whatFailureGroupHandlerFactory = new WhatFailureGroupHandlerFactory();

        $whatFailureGroupHandler = $whatFailureGroupHandlerFactory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $whatFailureGroupHandler);

        $fp = new ReflectionProperty($whatFailureGroupHandler, 'handlers');

        $handlerClasses = $fp->getValue($whatFailureGroupHandler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($whatFailureGroupHandler, 'bubble');

        self::assertFalse($bubble->getValue($whatFailureGroupHandler));

        $proc = new ReflectionProperty($whatFailureGroupHandler, 'processors');

        $processors = $proc->getValue($whatFailureGroupHandler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor1, $processors[0]);
        self::assertSame($processor2, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $handlers = [
            [
                'enabled' => false,
                'type' => FingersCrossedHandler::class,
            ],
            [
                'enabled' => true,
                'type' => FirePHPHandler::class,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

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

        $handler1 = $this->createMock(FirePHPHandler::class);
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->createMock(GelfHandler::class);
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('build')
            ->willReturnMap(
                [
                    [FirePHPHandler::class, [], $handler1],
                    [ChromePHPHandler::class, [], $handler2],
                    [GelfHandler::class, [], $handler3],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, null],
                ],
            );

        $whatFailureGroupHandlerFactory = new WhatFailureGroupHandlerFactory();

        $whatFailureGroupHandler = $whatFailureGroupHandlerFactory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $whatFailureGroupHandler);

        $fp = new ReflectionProperty($whatFailureGroupHandler, 'handlers');

        $handlerClasses = $fp->getValue($whatFailureGroupHandler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($whatFailureGroupHandler, 'bubble');

        self::assertFalse($bubble->getValue($whatFailureGroupHandler));

        $proc = new ReflectionProperty($whatFailureGroupHandler, 'processors');

        $processors = $proc->getValue($whatFailureGroupHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5WithoutCurl(): void
    {
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        if (!class_exists(MicrosoftTeamsHandler::class)) {
            self::markTestSkipped(
                'This test only is usefull if class MicrosoftTeamsHandler is available',
            );
        }

        $handlers = [
            [
                'enabled' => false,
                'type' => FingersCrossedHandler::class,
            ],
            [
                'enabled' => true,
                'type' => FirePHPHandler::class,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
            [
                'type' => MicrosoftTeamsHandler::class,
            ],
        ];

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

        $handler1 = $this->createMock(FirePHPHandler::class);
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->createMock(ChromePHPHandler::class);
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->createMock(GelfHandler::class);
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $handler4 = $this->createStub(MicrosoftTeamsHandler::class);

        $monologHandlerPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(4))
            ->method('build')
            ->willReturnMap(
                [
                    [FirePHPHandler::class, [], $handler1],
                    [ChromePHPHandler::class, [], $handler2],
                    [GelfHandler::class, [], $handler3],
                    [MicrosoftTeamsHandler::class, [], $handler4],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, null],
                ],
            );

        $whatFailureGroupHandlerFactory = new WhatFailureGroupHandlerFactory();

        $whatFailureGroupHandler = $whatFailureGroupHandlerFactory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $whatFailureGroupHandler);

        $fp = new ReflectionProperty($whatFailureGroupHandler, 'handlers');

        $handlerClasses = $fp->getValue($whatFailureGroupHandler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($whatFailureGroupHandler, 'bubble');

        self::assertFalse($bubble->getValue($whatFailureGroupHandler));

        $proc = new ReflectionProperty($whatFailureGroupHandler, 'processors');

        $processors = $proc->getValue($whatFailureGroupHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }
}
