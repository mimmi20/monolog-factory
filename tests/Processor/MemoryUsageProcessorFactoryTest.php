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

use Mimmi20\MonologFactory\Processor\MemoryUsageProcessorFactory;
use Monolog\Processor\MemoryUsageProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class MemoryUsageProcessorFactoryTest extends TestCase
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

        $factory = new MemoryUsageProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(MemoryUsageProcessor::class, $processor);

        $realUsage = new ReflectionProperty($processor, 'realUsage');

        self::assertTrue($realUsage->getValue($processor));

        $useFormatting = new ReflectionProperty($processor, 'useFormatting');

        self::assertTrue($useFormatting->getValue($processor));
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

        $factory = new MemoryUsageProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(MemoryUsageProcessor::class, $processor);

        $realUsage = new ReflectionProperty($processor, 'realUsage');

        self::assertTrue($realUsage->getValue($processor));

        $useFormatting = new ReflectionProperty($processor, 'useFormatting');

        self::assertTrue($useFormatting->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MemoryUsageProcessorFactory();

        $processor = $factory($container, '', ['realUsage' => false, 'useFormatting' => false]);

        self::assertInstanceOf(MemoryUsageProcessor::class, $processor);

        $realUsage = new ReflectionProperty($processor, 'realUsage');

        self::assertFalse($realUsage->getValue($processor));

        $useFormatting = new ReflectionProperty($processor, 'useFormatting');

        self::assertFalse($useFormatting->getValue($processor));
    }
}
