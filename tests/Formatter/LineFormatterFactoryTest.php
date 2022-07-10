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

namespace Mimmi20Test\MonologFactory\Formatter;

use Mimmi20\MonologFactory\Formatter\LineFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class LineFormatterFactoryTest extends TestCase
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

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');
        $format->setAccessible(true);

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));
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

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');
        $format->setAccessible(true);

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $format                     = '[abc] [def]';
        $dateFormat                 = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth          = 42;
        $maxNormalizeItemCount      = 4711;
        $allowInlineLineBreaks      = true;
        $ignoreEmptyContextAndExtra = true;
        $include                    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '', ['format' => $format, 'dateFormat' => $dateFormat, 'allowInlineLineBreaks' => $allowInlineLineBreaks, 'ignoreEmptyContextAndExtra' => $ignoreEmptyContextAndExtra, 'includeStacktraces' => $include, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertTrue($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));
    }
}
