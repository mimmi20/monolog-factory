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

use Mimmi20\MonologFactory\Formatter\LogmaticFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Formatter\NormalizerFormatter;
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

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $formatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $formatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $formatter->getBatchMode());
        self::assertTrue($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');

        self::assertSame('', $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appName');

        self::assertSame('', $a->getValue($formatter));
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

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $formatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $formatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $formatter->getBatchMode());
        self::assertTrue($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');

        self::assertSame('', $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appName');

        self::assertSame('', $a->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());
        self::assertSame($batchMode, $formatter->getBatchMode());
        self::assertFalse($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertTrue($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');

        self::assertSame($hostname, $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appName');

        self::assertSame($appname, $a->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true, 'ignoreEmptyContextAndExtra' => true]);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());
        self::assertSame($batchMode, $formatter->getBatchMode());
        self::assertFalse($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');

        self::assertTrue($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertTrue($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');

        self::assertSame($hostname, $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appName');

        self::assertSame($appname, $a->getValue($formatter));
    }
}
