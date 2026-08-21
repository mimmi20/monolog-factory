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

use Mimmi20\MonologFactory\Formatter\LineFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class LineFormatterFactoryTest extends TestCase
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

        $lineFormatterFactory = new LineFormatterFactory();

        $lineFormatter = $lineFormatterFactory($container, '');

        self::assertInstanceOf(LineFormatter::class, $lineFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $lineFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $lineFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $lineFormatter->getMaxNormalizeItemCount(),
        );

        $ailb = new ReflectionProperty($lineFormatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($lineFormatter));

        $format = new ReflectionProperty($lineFormatter, 'format');

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($lineFormatter));

        $ig = new ReflectionProperty($lineFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($lineFormatter));

        $st = new ReflectionProperty($lineFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($lineFormatter));
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

        $lineFormatterFactory = new LineFormatterFactory();

        $lineFormatter = $lineFormatterFactory($container, '', []);

        self::assertInstanceOf(LineFormatter::class, $lineFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $lineFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $lineFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $lineFormatter->getMaxNormalizeItemCount(),
        );

        $ailb = new ReflectionProperty($lineFormatter, 'allowInlineLineBreaks');

        self::assertFalse($ailb->getValue($lineFormatter));

        $format = new ReflectionProperty($lineFormatter, 'format');

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($lineFormatter));

        $ig = new ReflectionProperty($lineFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($lineFormatter));

        $st = new ReflectionProperty($lineFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($lineFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
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

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $lineFormatterFactory = new LineFormatterFactory();

        $lineFormatter = $lineFormatterFactory($container, '', ['format' => $format, 'dateFormat' => $dateFormat, 'allowInlineLineBreaks' => $allowInlineLineBreaks, 'ignoreEmptyContextAndExtra' => $ignoreEmptyContextAndExtra, 'includeStacktraces' => $include, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(LineFormatter::class, $lineFormatter);
        self::assertSame($dateFormat, $lineFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $lineFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $lineFormatter->getMaxNormalizeItemCount());

        $ailb = new ReflectionProperty($lineFormatter, 'allowInlineLineBreaks');

        self::assertTrue($ailb->getValue($lineFormatter));

        $formatP = new ReflectionProperty($lineFormatter, 'format');

        self::assertSame($format, $formatP->getValue($lineFormatter));

        $ig = new ReflectionProperty($lineFormatter, 'ignoreEmptyContextAndExtra');

        self::assertTrue($ig->getValue($lineFormatter));

        $st = new ReflectionProperty($lineFormatter, 'includeStacktraces');

        self::assertTrue($st->getValue($lineFormatter));
    }
}
