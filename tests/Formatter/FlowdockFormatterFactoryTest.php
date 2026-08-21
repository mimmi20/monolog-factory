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

namespace Mimmi20Test\MonologFactory\Formatter;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\FlowdockFormatterFactory;
use Monolog\Formatter\FlowdockFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class FlowdockFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $flowdockFormatterFactory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $flowdockFormatterFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutSource(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $flowdockFormatterFactory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No source provided');

        $flowdockFormatterFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutSourceEmail(): void
    {
        $source = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $flowdockFormatterFactory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No sourceEmail provided');

        $flowdockFormatterFactory($container, '', ['source' => $source]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithSouceAndSourceEmail(): void
    {
        $source      = 'abc';
        $sourceEmail = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $flowdockFormatterFactory = new FlowdockFormatterFactory();

        $flowdockFormatter = $flowdockFormatterFactory($container, '', ['source' => $source, 'sourceEmail' => $sourceEmail]);

        self::assertInstanceOf(FlowdockFormatter::class, $flowdockFormatter);

        $s = new ReflectionProperty($flowdockFormatter, 'source');

        self::assertSame($source, $s->getValue($flowdockFormatter));

        $se = new ReflectionProperty($flowdockFormatter, 'sourceEmail');

        self::assertSame($sourceEmail, $se->getValue($flowdockFormatter));
    }
}
