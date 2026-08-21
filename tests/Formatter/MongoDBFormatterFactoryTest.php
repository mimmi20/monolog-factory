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

namespace Mimmi20Test\MonologFactory\Formatter;

use Mimmi20\MonologFactory\Formatter\MongoDBFormatterFactory;
use Monolog\Formatter\MongoDBFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
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

        $mongoDBFormatterFactory = new MongoDBFormatterFactory();

        $mongoDBFormatter = $mongoDBFormatterFactory($container, '');

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBFormatter);

        $mnl = new ReflectionProperty($mongoDBFormatter, 'maxNestingLevel');

        self::assertSame(
            MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL,
            $mnl->getValue($mongoDBFormatter),
        );

        $ts = new ReflectionProperty($mongoDBFormatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($mongoDBFormatter));
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

        $mongoDBFormatterFactory = new MongoDBFormatterFactory();

        $mongoDBFormatter = $mongoDBFormatterFactory($container, '', []);

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBFormatter);

        $mnl = new ReflectionProperty($mongoDBFormatter, 'maxNestingLevel');

        self::assertSame(
            MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL,
            $mnl->getValue($mongoDBFormatter),
        );

        $ts = new ReflectionProperty($mongoDBFormatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($mongoDBFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $maxNestingLevel        = 42;
        $exceptionTraceAsString = false;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBFormatterFactory = new MongoDBFormatterFactory();

        $mongoDBFormatter = $mongoDBFormatterFactory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBFormatter);

        $mnl = new ReflectionProperty($mongoDBFormatter, 'maxNestingLevel');

        self::assertSame($maxNestingLevel, $mnl->getValue($mongoDBFormatter));

        $ts = new ReflectionProperty($mongoDBFormatter, 'exceptionTraceAsString');

        self::assertFalse($ts->getValue($mongoDBFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $maxNestingLevel        = -42;
        $exceptionTraceAsString = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mongoDBFormatterFactory = new MongoDBFormatterFactory();

        $mongoDBFormatter = $mongoDBFormatterFactory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $mongoDBFormatter);

        $mnl = new ReflectionProperty($mongoDBFormatter, 'maxNestingLevel');

        self::assertSame(0, $mnl->getValue($mongoDBFormatter));

        $ts = new ReflectionProperty($mongoDBFormatter, 'exceptionTraceAsString');

        self::assertTrue($ts->getValue($mongoDBFormatter));
    }
}
