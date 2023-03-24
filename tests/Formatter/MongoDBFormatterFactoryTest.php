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

use Mimmi20\MonologFactory\Formatter\MongoDBFormatterFactory;
use Monolog\Formatter\MongoDBFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class MongoDBFormatterFactoryTest extends TestCase
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

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');

        self::assertSame(MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($formatter));
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

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');

        self::assertSame(MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $maxNestingLevel        = 42;
        $exceptionTraceAsString = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');

        self::assertSame($maxNestingLevel, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');

        self::assertFalse($ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig2(): void
    {
        $maxNestingLevel        = -42;
        $exceptionTraceAsString = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');

        self::assertSame(0, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($formatter));
    }
}
