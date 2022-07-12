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

use Mimmi20\Monolog\Formatter\StreamFormatter;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Mimmi20\MonologFactory\Formatter\StreamFormatterFactory;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class StreamFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
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

        $factory = new StreamFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(StreamFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');

        self::assertSame(StreamFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame(StreamFormatter::BOX_STYLE, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
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

        $factory = new StreamFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(StreamFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');

        self::assertSame(StreamFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertFalse($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame(StreamFormatter::BOX_STYLE, $ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $format                = '[abc] [def]';
        $dateFormat            = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;
        $allowInlineLineBreaks = true;
        $include               = true;
        $tableStyle            = 'borderless';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new StreamFormatterFactory();

        $formatter = $factory($container, '', ['format' => $format, 'tableStyle' => $tableStyle, 'dateFormat' => $dateFormat, 'allowInlineLineBreaks' => $allowInlineLineBreaks, 'includeStacktraces' => $include, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(StreamFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');

        self::assertSame($format, $formatP->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');

        self::assertTrue($st->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'tableStyle');

        self::assertSame($tableStyle, $ts->getValue($formatter));
    }
}
