<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Formatter;

use Mimmi20\MonologFactory\Formatter\FluentdFormatterFactory;
use Monolog\Formatter\FluentdFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class FluentdFormatterFactoryTest extends TestCase
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

        $factory = new FluentdFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(FluentdFormatter::class, $formatter);
        self::assertFalse($formatter->isUsingLevelsInTag());
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

        $factory = new FluentdFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(FluentdFormatter::class, $formatter);
        self::assertFalse($formatter->isUsingLevelsInTag());
    }

    /** @throws Exception */
    public function testInvokeWithConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FluentdFormatterFactory();

        $formatter = $factory($container, '', ['levelTag' => true]);

        self::assertInstanceOf(FluentdFormatter::class, $formatter);
        self::assertTrue($formatter->isUsingLevelsInTag());
    }
}
