<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Formatter;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\FlowdockFormatterFactory;
use Monolog\Formatter\FlowdockFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class FlowdockFormatterFactoryTest extends TestCase
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

        $factory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /** @throws Exception */
    public function testInvokeWithoutSource(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No source provided');

        $factory($container, '', []);
    }

    /** @throws Exception */
    public function testInvokeWithoutSourceEmail(): void
    {
        $source = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FlowdockFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No sourceEmail provided');

        $factory($container, '', ['source' => $source]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithSouceAndSourceEmail(): void
    {
        $source      = 'abc';
        $sourceEmail = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FlowdockFormatterFactory();

        $formatter = $factory($container, '', ['source' => $source, 'sourceEmail' => $sourceEmail]);

        self::assertInstanceOf(FlowdockFormatter::class, $formatter);

        $s = new ReflectionProperty($formatter, 'source');

        self::assertSame($source, $s->getValue($formatter));

        $se = new ReflectionProperty($formatter, 'sourceEmail');

        self::assertSame($sourceEmail, $se->getValue($formatter));
    }
}
