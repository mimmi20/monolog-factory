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

use Mimmi20\MonologFactory\Formatter\JsonFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class JsonFormatterFactoryTest extends TestCase
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

        $jsonFormatterFactory = new JsonFormatterFactory();

        $jsonFormatter = $jsonFormatterFactory($container, '');

        self::assertInstanceOf(JsonFormatter::class, $jsonFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $jsonFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $jsonFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $jsonFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $jsonFormatter->getBatchMode());
        self::assertTrue($jsonFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($jsonFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($jsonFormatter));

        $st = new ReflectionProperty($jsonFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($jsonFormatter));
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

        $jsonFormatterFactory = new JsonFormatterFactory();

        $jsonFormatter = $jsonFormatterFactory($container, '', []);

        self::assertInstanceOf(JsonFormatter::class, $jsonFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $jsonFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $jsonFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $jsonFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $jsonFormatter->getBatchMode());
        self::assertTrue($jsonFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($jsonFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($jsonFormatter));

        $st = new ReflectionProperty($jsonFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($jsonFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $batchMode                  = JsonFormatter::BATCH_MODE_NEWLINES;
        $appendNewline              = false;
        $ignoreEmptyContextAndExtra = true;
        $include                    = true;
        $dateFormat                 = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth          = 42;
        $maxNormalizeItemCount      = 4711;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $jsonFormatterFactory = new JsonFormatterFactory();

        $jsonFormatter = $jsonFormatterFactory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'ignoreEmptyContextAndExtra' => $ignoreEmptyContextAndExtra, 'includeStacktraces' => $include, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(JsonFormatter::class, $jsonFormatter);
        self::assertSame($dateFormat, $jsonFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $jsonFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $jsonFormatter->getMaxNormalizeItemCount());
        self::assertSame($batchMode, $jsonFormatter->getBatchMode());
        self::assertFalse($jsonFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($jsonFormatter, 'ignoreEmptyContextAndExtra');

        self::assertTrue($ig->getValue($jsonFormatter));

        $st = new ReflectionProperty($jsonFormatter, 'includeStacktraces');

        self::assertTrue($st->getValue($jsonFormatter));
    }
}
