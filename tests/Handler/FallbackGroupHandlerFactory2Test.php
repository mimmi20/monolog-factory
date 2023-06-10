<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Handler;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\FallbackGroupHandlerFactory;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class FallbackGroupHandlerFactory2Test extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors2(): void
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

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
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
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
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndProcessors3(): void
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
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['abc', [], $processor1],
                    ['xyz', ['efg' => 'ijk'], $processor2],
                ],
            );

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
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
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
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $factory = new FallbackGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);

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
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors4(): void
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');

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
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
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
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager) {
                    if ($var === MonologHandlerPluginManager::class) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors5(): void
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
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
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
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologProcessorPluginManager::class, null],
                ],
            );

        $factory = new FallbackGroupHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);
    }
}
