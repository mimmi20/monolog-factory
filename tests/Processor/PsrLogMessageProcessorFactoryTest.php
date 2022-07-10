<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
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
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class PsrLogMessageProcessorFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dateFormatP->setAccessible(true);

        self::assertNull($dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');
        $rucf->setAccessible(true);

        self::assertFalse($rucf->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dateFormatP->setAccessible(true);

        self::assertNull($dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');
        $rucf->setAccessible(true);

        self::assertFalse($rucf->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dateFormatP->setAccessible(true);

        self::assertSame($dateFormat, $dateFormatP->getValue($processor));

        $rucf = new ReflectionProperty($processor, 'removeUsedContextFields');
        $rucf->setAccessible(true);

        self::assertTrue($rucf->getValue($processor));
    }
}
