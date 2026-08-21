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

namespace Mimmi20Test\MonologFactory\Processor;

use Mimmi20\MonologFactory\Processor\IntrospectionProcessorFactory;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

final class IntrospectionProcessorFactoryTest extends TestCase
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

        $introspectionProcessorFactory = new IntrospectionProcessorFactory();

        $introspectionProcessor = $introspectionProcessorFactory($container, '');

        self::assertInstanceOf(IntrospectionProcessor::class, $introspectionProcessor);

        $lvl = new ReflectionProperty($introspectionProcessor, 'level');

        self::assertSame(Level::Debug, $lvl->getValue($introspectionProcessor));

        $scp = new ReflectionProperty($introspectionProcessor, 'skipClassesPartials');

        self::assertSame(['Monolog\\'], $scp->getValue($introspectionProcessor));

        $ssfc = new ReflectionProperty($introspectionProcessor, 'skipStackFramesCount');

        self::assertSame(0, $ssfc->getValue($introspectionProcessor));
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

        $introspectionProcessorFactory = new IntrospectionProcessorFactory();

        $introspectionProcessor = $introspectionProcessorFactory($container, '', []);

        self::assertInstanceOf(IntrospectionProcessor::class, $introspectionProcessor);

        $lvl = new ReflectionProperty($introspectionProcessor, 'level');

        self::assertSame(Level::Debug, $lvl->getValue($introspectionProcessor));

        $scp = new ReflectionProperty($introspectionProcessor, 'skipClassesPartials');

        self::assertSame(['Monolog\\'], $scp->getValue($introspectionProcessor));

        $ssfc = new ReflectionProperty($introspectionProcessor, 'skipStackFramesCount');

        self::assertSame(0, $ssfc->getValue($introspectionProcessor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $level                = LogLevel::ALERT;
        $skipClassesPartials  = ['Laminas\\'];
        $skipStackFramesCount = 42;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $introspectionProcessorFactory = new IntrospectionProcessorFactory();

        $introspectionProcessor = $introspectionProcessorFactory($container, '', ['level' => $level, 'skipClassesPartials' => $skipClassesPartials, 'skipStackFramesCount' => $skipStackFramesCount]);

        self::assertInstanceOf(IntrospectionProcessor::class, $introspectionProcessor);

        $lvl = new ReflectionProperty($introspectionProcessor, 'level');

        self::assertSame(Level::Alert, $lvl->getValue($introspectionProcessor));

        $scp = new ReflectionProperty($introspectionProcessor, 'skipClassesPartials');

        self::assertSame(['Monolog\\', 'Laminas\\'], $scp->getValue($introspectionProcessor));

        $ssfc = new ReflectionProperty($introspectionProcessor, 'skipStackFramesCount');

        self::assertSame($skipStackFramesCount, $ssfc->getValue($introspectionProcessor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $level                = LogLevel::ALERT;
        $skipClassesPartials  = 'Laminas\\';
        $skipStackFramesCount = 42;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $introspectionProcessorFactory = new IntrospectionProcessorFactory();

        $introspectionProcessor = $introspectionProcessorFactory($container, '', ['level' => $level, 'skipClassesPartials' => $skipClassesPartials, 'skipStackFramesCount' => $skipStackFramesCount]);

        self::assertInstanceOf(IntrospectionProcessor::class, $introspectionProcessor);

        $lvl = new ReflectionProperty($introspectionProcessor, 'level');

        self::assertSame(Level::Alert, $lvl->getValue($introspectionProcessor));

        $scp = new ReflectionProperty($introspectionProcessor, 'skipClassesPartials');

        self::assertSame(['Monolog\\', 'Laminas\\'], $scp->getValue($introspectionProcessor));

        $ssfc = new ReflectionProperty($introspectionProcessor, 'skipStackFramesCount');

        self::assertSame($skipStackFramesCount, $ssfc->getValue($introspectionProcessor));
    }
}
