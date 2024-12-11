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

use InvalidArgumentException;
use Mimmi20\MonologFactory\Processor\UidProcessorFactory;
use Monolog\Processor\UidProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class UidProcessorFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new UidProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(UidProcessor::class, $processor);
    }

    /** @throws Exception */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new UidProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(UidProcessor::class, $processor);
    }

    /** @throws Exception */
    public function testInvokeWithLength(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new UidProcessorFactory();

        $processor = $factory($container, '', ['length' => 22]);

        self::assertInstanceOf(UidProcessor::class, $processor);
    }

    /** @throws Exception */
    public function testInvokeWithLengthTooShort(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new UidProcessorFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The uid length must be an integer between 1 and 32');

        $factory($container, '', ['length' => 0]);
    }

    /** @throws Exception */
    public function testInvokeWithLengthTooLong(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new UidProcessorFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The uid length must be an integer between 1 and 32');

        $factory($container, '', ['length' => 33]);
    }
}
