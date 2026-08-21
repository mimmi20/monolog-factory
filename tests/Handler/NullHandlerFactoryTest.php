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

use Mimmi20\MonologFactory\Handler\NullHandlerFactory;
use Monolog\Handler\NullHandler;
use Monolog\Level;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

final class NullHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $nullHandlerFactory = new NullHandlerFactory();

        $nullHandler = $nullHandlerFactory($container, '');

        self::assertInstanceOf(NullHandler::class, $nullHandler);

        $reflectionProperty = new ReflectionProperty($nullHandler, 'level');

        self::assertSame(Level::Debug, $reflectionProperty->getValue($nullHandler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $nullHandlerFactory = new NullHandlerFactory();

        $nullHandler = $nullHandlerFactory($container, '', []);

        self::assertInstanceOf(NullHandler::class, $nullHandler);

        $reflectionProperty = new ReflectionProperty($nullHandler, 'level');

        self::assertSame(Level::Debug, $reflectionProperty->getValue($nullHandler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nullHandlerFactory = new NullHandlerFactory();

        $nullHandler = $nullHandlerFactory($container, '', ['level' => LogLevel::ALERT]);

        self::assertInstanceOf(NullHandler::class, $nullHandler);

        $reflectionProperty = new ReflectionProperty($nullHandler, 'level');

        self::assertSame(Level::Alert, $reflectionProperty->getValue($nullHandler));
    }
}
