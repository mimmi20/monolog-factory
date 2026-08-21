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

use Mimmi20\MonologFactory\Processor\MemoryPeakUsageProcessorFactory;
use Monolog\Processor\MemoryPeakUsageProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class MemoryPeakUsageProcessorFactoryTest extends TestCase
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

        $memoryPeakUsageProcessorFactory = new MemoryPeakUsageProcessorFactory();

        $memoryPeakUsageProcessor = $memoryPeakUsageProcessorFactory($container, '');

        self::assertInstanceOf(MemoryPeakUsageProcessor::class, $memoryPeakUsageProcessor);

        $realUsage = new ReflectionProperty($memoryPeakUsageProcessor, 'realUsage');

        self::assertTrue($realUsage->getValue($memoryPeakUsageProcessor));

        $useFormatting = new ReflectionProperty($memoryPeakUsageProcessor, 'useFormatting');

        self::assertTrue($useFormatting->getValue($memoryPeakUsageProcessor));
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

        $memoryPeakUsageProcessorFactory = new MemoryPeakUsageProcessorFactory();

        $memoryPeakUsageProcessor = $memoryPeakUsageProcessorFactory($container, '', []);

        self::assertInstanceOf(MemoryPeakUsageProcessor::class, $memoryPeakUsageProcessor);

        $realUsage = new ReflectionProperty($memoryPeakUsageProcessor, 'realUsage');

        self::assertTrue($realUsage->getValue($memoryPeakUsageProcessor));

        $useFormatting = new ReflectionProperty($memoryPeakUsageProcessor, 'useFormatting');

        self::assertTrue($useFormatting->getValue($memoryPeakUsageProcessor));
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

        $memoryPeakUsageProcessorFactory = new MemoryPeakUsageProcessorFactory();

        $memoryPeakUsageProcessor = $memoryPeakUsageProcessorFactory($container, '', ['realUsage' => false, 'useFormatting' => false]);

        self::assertInstanceOf(MemoryPeakUsageProcessor::class, $memoryPeakUsageProcessor);

        $realUsage = new ReflectionProperty($memoryPeakUsageProcessor, 'realUsage');

        self::assertFalse($realUsage->getValue($memoryPeakUsageProcessor));

        $useFormatting = new ReflectionProperty($memoryPeakUsageProcessor, 'useFormatting');

        self::assertFalse($useFormatting->getValue($memoryPeakUsageProcessor));
    }
}
