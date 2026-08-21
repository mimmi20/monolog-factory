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

use Mimmi20\MonologFactory\Formatter\LogmaticFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class LogmaticFormatterFactoryTest extends TestCase
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

        $logmaticFormatterFactory = new LogmaticFormatterFactory();

        $logmaticFormatter = $logmaticFormatterFactory($container, '');

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $logmaticFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $logmaticFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $logmaticFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $logmaticFormatter->getBatchMode());
        self::assertTrue($logmaticFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($logmaticFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($logmaticFormatter));

        $st = new ReflectionProperty($logmaticFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($logmaticFormatter));

        $h = new ReflectionProperty($logmaticFormatter, 'hostname');

        self::assertSame('', $h->getValue($logmaticFormatter));

        $a = new ReflectionProperty($logmaticFormatter, 'appName');

        self::assertSame('', $a->getValue($logmaticFormatter));
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

        $logmaticFormatterFactory = new LogmaticFormatterFactory();

        $logmaticFormatter = $logmaticFormatterFactory($container, '', []);

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $logmaticFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $logmaticFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $logmaticFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $logmaticFormatter->getBatchMode());
        self::assertTrue($logmaticFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($logmaticFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($logmaticFormatter));

        $st = new ReflectionProperty($logmaticFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($logmaticFormatter));

        $h = new ReflectionProperty($logmaticFormatter, 'hostname');

        self::assertSame('', $h->getValue($logmaticFormatter));

        $a = new ReflectionProperty($logmaticFormatter, 'appName');

        self::assertSame('', $a->getValue($logmaticFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $batchMode             = JsonFormatter::BATCH_MODE_NEWLINES;
        $appendNewline         = false;
        $include               = true;
        $hostname              = 'abc';
        $appname               = 'xyz';
        $dateFormat            = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticFormatterFactory = new LogmaticFormatterFactory();

        $logmaticFormatter = $logmaticFormatterFactory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticFormatter);
        self::assertSame($dateFormat, $logmaticFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $logmaticFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $logmaticFormatter->getMaxNormalizeItemCount());
        self::assertSame($batchMode, $logmaticFormatter->getBatchMode());
        self::assertFalse($logmaticFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($logmaticFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($logmaticFormatter));

        $st = new ReflectionProperty($logmaticFormatter, 'includeStacktraces');

        self::assertTrue($st->getValue($logmaticFormatter));

        $h = new ReflectionProperty($logmaticFormatter, 'hostname');

        self::assertSame($hostname, $h->getValue($logmaticFormatter));

        $a = new ReflectionProperty($logmaticFormatter, 'appName');

        self::assertSame($appname, $a->getValue($logmaticFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $batchMode             = JsonFormatter::BATCH_MODE_JSON;
        $appendNewline         = false;
        $include               = true;
        $hostname              = 'abc';
        $appname               = 'xyz';
        $dateFormat            = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logmaticFormatterFactory = new LogmaticFormatterFactory();

        $logmaticFormatter = $logmaticFormatterFactory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true, 'ignoreEmptyContextAndExtra' => true]);

        self::assertInstanceOf(LogmaticFormatter::class, $logmaticFormatter);
        self::assertSame($dateFormat, $logmaticFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $logmaticFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $logmaticFormatter->getMaxNormalizeItemCount());
        self::assertSame($batchMode, $logmaticFormatter->getBatchMode());
        self::assertFalse($logmaticFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($logmaticFormatter, 'ignoreEmptyContextAndExtra');

        self::assertTrue($ig->getValue($logmaticFormatter));

        $st = new ReflectionProperty($logmaticFormatter, 'includeStacktraces');

        self::assertTrue($st->getValue($logmaticFormatter));

        $h = new ReflectionProperty($logmaticFormatter, 'hostname');

        self::assertSame($hostname, $h->getValue($logmaticFormatter));

        $a = new ReflectionProperty($logmaticFormatter, 'appName');

        self::assertSame($appname, $a->getValue($logmaticFormatter));
    }
}
