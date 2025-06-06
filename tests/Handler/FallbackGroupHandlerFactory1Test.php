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

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\FallbackGroupHandlerFactory;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class FallbackGroupHandlerFactory1Test extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new FallbackGroupHandlerFactory();

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
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service names provided for the required handler classes');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithEmptyConfig2(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service names provided for the required handler classes');

        $factory($container, '', ['handlers' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutHandlers(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handlers specified');

        $factory($container, '', ['handlers' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithStringHandlers(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handlers' => ['test']]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerWithoutType(): void
    {
        $handlers = [[]];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handlers' => $handlers]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerWithType(): void
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

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $matcher = self::exactly(3);
        $monologHandlerPluginManager->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $handler1, $handler2): HandlerInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(FirePHPHandler::class, $id, (string) $invocation),
                        2 => self::assertSame(ChromePHPHandler::class, $id, (string) $invocation),
                        default => self::assertSame(GelfHandler::class, $id, (string) $invocation),
                    };

                    return match ($invocation) {
                        1 => $handler1,
                        2 => $handler2,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(2, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($bubble->getValue($handler));

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
    public function testInvokeWithHandlerWithType2(): void
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

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    [FirePHPHandler::class, [], $handler1],
                    [ChromePHPHandler::class, [], $handler2],
                ],
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $matcher = self::exactly(3);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $monologHandlerPluginManager): AbstractPluginManager {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertSame(MonologHandlerPluginManager::class, $id, (string) $invocation);

                    return match ($invocation) {
                        1, 2 => $monologHandlerPluginManager,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(2, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($bubble->getValue($handler));

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
    public function testInvokeWithHandlerWithType3(): void
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

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($bubble->getValue($handler));

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
    public function testInvokeWithHandlerWithType4(): void
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

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bubble->getValue($handler));

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
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $handlers  = [
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
        $formatter = true;

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bubble->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter(): void
    {
        $handlers  = [
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
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bubble->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $handlers  = [
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
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(FallbackGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bubble->getValue($handler));

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
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $handlers   = [
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
        $processors = true;

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);
    }
}
