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

use Mimmi20\MonologFactory\Processor\PsrLogMessageProcessorFactory;
use Monolog\Processor\PsrLogMessageProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class PsrLogMessageProcessorFactoryTest extends TestCase
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

        $psrLogMessageProcessorFactory = new PsrLogMessageProcessorFactory();

        $psrLogMessageProcessor = $psrLogMessageProcessorFactory($container, '');

        self::assertInstanceOf(PsrLogMessageProcessor::class, $psrLogMessageProcessor);

        $dateFormatP = new ReflectionProperty($psrLogMessageProcessor, 'dateFormat');

        self::assertNull($dateFormatP->getValue($psrLogMessageProcessor));

        $rucf = new ReflectionProperty($psrLogMessageProcessor, 'removeUsedContextFields');

        self::assertFalse($rucf->getValue($psrLogMessageProcessor));
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

        $psrLogMessageProcessorFactory = new PsrLogMessageProcessorFactory();

        $psrLogMessageProcessor = $psrLogMessageProcessorFactory($container, '', []);

        self::assertInstanceOf(PsrLogMessageProcessor::class, $psrLogMessageProcessor);

        $dateFormatP = new ReflectionProperty($psrLogMessageProcessor, 'dateFormat');

        self::assertNull($dateFormatP->getValue($psrLogMessageProcessor));

        $rucf = new ReflectionProperty($psrLogMessageProcessor, 'removeUsedContextFields');

        self::assertFalse($rucf->getValue($psrLogMessageProcessor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $dateFormat = 'c';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $psrLogMessageProcessorFactory = new PsrLogMessageProcessorFactory();

        $psrLogMessageProcessor = $psrLogMessageProcessorFactory($container, '', ['dateFormat' => $dateFormat, 'removeUsedContextFields' => true]);

        self::assertInstanceOf(PsrLogMessageProcessor::class, $psrLogMessageProcessor);

        $dateFormatP = new ReflectionProperty($psrLogMessageProcessor, 'dateFormat');

        self::assertSame($dateFormat, $dateFormatP->getValue($psrLogMessageProcessor));

        $rucf = new ReflectionProperty($psrLogMessageProcessor, 'removeUsedContextFields');

        self::assertTrue($rucf->getValue($psrLogMessageProcessor));
    }
}
