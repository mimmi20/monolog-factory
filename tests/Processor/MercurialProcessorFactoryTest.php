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

use Mimmi20\MonologFactory\Processor\MercurialProcessorFactory;
use Monolog\Level;
use Monolog\Processor\MercurialProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

final class MercurialProcessorFactoryTest extends TestCase
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

        $mercurialProcessorFactory = new MercurialProcessorFactory();

        $mercurialProcessor = $mercurialProcessorFactory($container, '');

        self::assertInstanceOf(MercurialProcessor::class, $mercurialProcessor);

        $reflectionProperty = new ReflectionProperty($mercurialProcessor, 'level');

        self::assertSame(Level::Debug, $reflectionProperty->getValue($mercurialProcessor));
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

        $mercurialProcessorFactory = new MercurialProcessorFactory();

        $mercurialProcessor = $mercurialProcessorFactory($container, '', []);

        self::assertInstanceOf(MercurialProcessor::class, $mercurialProcessor);

        $reflectionProperty = new ReflectionProperty($mercurialProcessor, 'level');

        self::assertSame(Level::Debug, $reflectionProperty->getValue($mercurialProcessor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithLevel(): void
    {
        $level = LogLevel::ALERT;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mercurialProcessorFactory = new MercurialProcessorFactory();

        $mercurialProcessor = $mercurialProcessorFactory($container, '', ['level' => $level]);

        self::assertInstanceOf(MercurialProcessor::class, $mercurialProcessor);

        $reflectionProperty = new ReflectionProperty($mercurialProcessor, 'level');

        self::assertSame(Level::Alert, $reflectionProperty->getValue($mercurialProcessor));
    }
}
