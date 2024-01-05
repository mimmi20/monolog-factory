<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Processor;

use Mimmi20\MonologFactory\Processor\PsrLogMessageProcessorFactory;
use Monolog\Processor\PsrLogMessageProcessor;
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

        $factory = new PsrLogMessageProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(PsrLogMessageProcessor::class, $processor);

        $dateFormatP = new ReflectionProperty($processor, 'dateFormat');

        self::assertNull($dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');

        self::assertFalse($rucf->getValue($processor));
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

        $factory = new PsrLogMessageProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(PsrLogMessageProcessor::class, $processor);

        $dateFormatP = new ReflectionProperty($processor, 'dateFormat');

        self::assertNull($dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');

        self::assertFalse($rucf->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $dateFormat = 'c';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PsrLogMessageProcessorFactory();

        $processor = $factory($container, '', ['dateFormat' => $dateFormat, 'removeUsedContextFields' => true]);

        self::assertInstanceOf(PsrLogMessageProcessor::class, $processor);

        $dateFormatP = new ReflectionProperty($processor, 'dateFormat');

        self::assertSame($dateFormat, $dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');

        self::assertTrue($rucf->getValue($processor));
    }
}
