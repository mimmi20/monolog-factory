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

namespace Mimmi20Test\MonologFactory\Processor;

use Mimmi20\MonologFactory\Processor\IntrospectionProcessorFactory;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;
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

        $factory = new IntrospectionProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(IntrospectionProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');

        self::assertSame(Level::Debug, $lvl->getValue($processor));

        $scp = new ReflectionProperty($processor, 'skipClassesPartials');

        self::assertSame(['Monolog\\'], $scp->getValue($processor));

        $ssfc = new ReflectionProperty($processor, 'skipStackFramesCount');

        self::assertSame(0, $ssfc->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new IntrospectionProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(IntrospectionProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');

        self::assertSame(Level::Debug, $lvl->getValue($processor));

        $scp = new ReflectionProperty($processor, 'skipClassesPartials');

        self::assertSame(['Monolog\\'], $scp->getValue($processor));

        $ssfc = new ReflectionProperty($processor, 'skipStackFramesCount');

        self::assertSame(0, $ssfc->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $level                = LogLevel::ALERT;
        $skipClassesPartials  = ['Laminas\\'];
        $skipStackFramesCount = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new IntrospectionProcessorFactory();

        $processor = $factory($container, '', ['level' => $level, 'skipClassesPartials' => $skipClassesPartials, 'skipStackFramesCount' => $skipStackFramesCount]);

        self::assertInstanceOf(IntrospectionProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');

        self::assertSame(Level::Alert, $lvl->getValue($processor));

        $scp = new ReflectionProperty($processor, 'skipClassesPartials');

        self::assertSame(['Monolog\\', 'Laminas\\'], $scp->getValue($processor));

        $ssfc = new ReflectionProperty($processor, 'skipStackFramesCount');

        self::assertSame($skipStackFramesCount, $ssfc->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig2(): void
    {
        $level                = LogLevel::ALERT;
        $skipClassesPartials  = 'Laminas\\';
        $skipStackFramesCount = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new IntrospectionProcessorFactory();

        $processor = $factory($container, '', ['level' => $level, 'skipClassesPartials' => $skipClassesPartials, 'skipStackFramesCount' => $skipStackFramesCount]);

        self::assertInstanceOf(IntrospectionProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');

        self::assertSame(Level::Alert, $lvl->getValue($processor));

        $scp = new ReflectionProperty($processor, 'skipClassesPartials');

        self::assertSame(['Monolog\\', 'Laminas\\'], $scp->getValue($processor));

        $ssfc = new ReflectionProperty($processor, 'skipStackFramesCount');

        self::assertSame($skipStackFramesCount, $ssfc->getValue($processor));
    }
}
