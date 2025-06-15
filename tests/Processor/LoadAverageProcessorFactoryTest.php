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

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Processor\LoadAverageProcessorFactory;
use Monolog\Processor\LoadAverageProcessor;
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

        $factory = new LoadAverageProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(LoadAverageProcessor::class, $processor);

        $asl = new ReflectionProperty($processor, 'avgSystemLoad');

        self::assertSame(LoadAverageProcessor::LOAD_1_MINUTE, $asl->getValue($processor));
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

        $factory = new LoadAverageProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(LoadAverageProcessor::class, $processor);

        $asl = new ReflectionProperty($processor, 'avgSystemLoad');

        self::assertSame(LoadAverageProcessor::LOAD_1_MINUTE, $asl->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithLoad(): void
    {
        $load = LoadAverageProcessor::LOAD_5_MINUTE;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LoadAverageProcessorFactory();

        $processor = $factory($container, '', ['load' => $load]);

        self::assertInstanceOf(LoadAverageProcessor::class, $processor);

        $asl = new ReflectionProperty($processor, 'avgSystemLoad');

        self::assertSame($load, $asl->getValue($processor));
    }

    /** @throws Exception */
    public function testInvokeWithLoadWithWrongValue(): void
    {
        $load = 123;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LoadAverageProcessorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create service %s', LoadAverageProcessor::class),
        );

        $factory($container, '', ['load' => $load]);
    }
}
