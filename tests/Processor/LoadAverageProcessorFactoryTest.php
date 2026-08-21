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

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Processor\LoadAverageProcessorFactory;
use Monolog\Processor\LoadAverageProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class LoadAverageProcessorFactoryTest extends TestCase
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

        $loadAverageProcessorFactory = new LoadAverageProcessorFactory();

        $loadAverageProcessor = $loadAverageProcessorFactory($container, '');

        self::assertInstanceOf(LoadAverageProcessor::class, $loadAverageProcessor);

        $reflectionProperty = new ReflectionProperty($loadAverageProcessor, 'avgSystemLoad');

        self::assertSame(
            LoadAverageProcessor::LOAD_1_MINUTE,
            $reflectionProperty->getValue($loadAverageProcessor),
        );
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

        $loadAverageProcessorFactory = new LoadAverageProcessorFactory();

        $loadAverageProcessor = $loadAverageProcessorFactory($container, '', []);

        self::assertInstanceOf(LoadAverageProcessor::class, $loadAverageProcessor);

        $reflectionProperty = new ReflectionProperty($loadAverageProcessor, 'avgSystemLoad');

        self::assertSame(
            LoadAverageProcessor::LOAD_1_MINUTE,
            $reflectionProperty->getValue($loadAverageProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithLoad(): void
    {
        $load = LoadAverageProcessor::LOAD_5_MINUTE;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $loadAverageProcessorFactory = new LoadAverageProcessorFactory();

        $loadAverageProcessor = $loadAverageProcessorFactory($container, '', ['load' => $load]);

        self::assertInstanceOf(LoadAverageProcessor::class, $loadAverageProcessor);

        $reflectionProperty = new ReflectionProperty($loadAverageProcessor, 'avgSystemLoad');

        self::assertSame($load, $reflectionProperty->getValue($loadAverageProcessor));
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithLoadWithWrongValue(): void
    {
        $load = 123;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $loadAverageProcessorFactory = new LoadAverageProcessorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create service %s', LoadAverageProcessor::class),
        );

        $loadAverageProcessorFactory($container, '', ['load' => $load]);
    }
}
