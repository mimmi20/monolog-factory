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

use DateTimeInterface;
use Mimmi20\MonologFactory\Formatter\GoogleCloudLoggingFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class GoogleCloudLoggingFormatterFactoryTest extends TestCase
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

        $googleCloudLoggingFormatterFactory = new GoogleCloudLoggingFormatterFactory();

        $googleCloudLoggingFormatter = $googleCloudLoggingFormatterFactory($container, '');

        self::assertInstanceOf(GoogleCloudLoggingFormatter::class, $googleCloudLoggingFormatter);
        self::assertSame(
            DateTimeInterface::RFC3339_EXTENDED,
            $googleCloudLoggingFormatter->getDateFormat(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $googleCloudLoggingFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $googleCloudLoggingFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(
            GoogleCloudLoggingFormatter::BATCH_MODE_JSON,
            $googleCloudLoggingFormatter->getBatchMode(),
        );
        self::assertTrue($googleCloudLoggingFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($googleCloudLoggingFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($googleCloudLoggingFormatter));

        $st = new ReflectionProperty($googleCloudLoggingFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($googleCloudLoggingFormatter));
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

        $googleCloudLoggingFormatterFactory = new GoogleCloudLoggingFormatterFactory();

        $googleCloudLoggingFormatter = $googleCloudLoggingFormatterFactory($container, '', []);

        self::assertInstanceOf(GoogleCloudLoggingFormatter::class, $googleCloudLoggingFormatter);
        self::assertSame(
            DateTimeInterface::RFC3339_EXTENDED,
            $googleCloudLoggingFormatter->getDateFormat(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $googleCloudLoggingFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $googleCloudLoggingFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame(
            GoogleCloudLoggingFormatter::BATCH_MODE_JSON,
            $googleCloudLoggingFormatter->getBatchMode(),
        );
        self::assertTrue($googleCloudLoggingFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($googleCloudLoggingFormatter, 'ignoreEmptyContextAndExtra');

        self::assertFalse($ig->getValue($googleCloudLoggingFormatter));

        $st = new ReflectionProperty($googleCloudLoggingFormatter, 'includeStacktraces');

        self::assertFalse($st->getValue($googleCloudLoggingFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $batchMode                  = GoogleCloudLoggingFormatter::BATCH_MODE_NEWLINES;
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

        $googleCloudLoggingFormatterFactory = new GoogleCloudLoggingFormatterFactory();

        $googleCloudLoggingFormatter = $googleCloudLoggingFormatterFactory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'ignoreEmptyContextAndExtra' => $ignoreEmptyContextAndExtra, 'includeStacktraces' => $include, 'dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(GoogleCloudLoggingFormatter::class, $googleCloudLoggingFormatter);
        self::assertSame($dateFormat, $googleCloudLoggingFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $googleCloudLoggingFormatter->getMaxNormalizeDepth());
        self::assertSame(
            $maxNormalizeItemCount,
            $googleCloudLoggingFormatter->getMaxNormalizeItemCount(),
        );
        self::assertSame($batchMode, $googleCloudLoggingFormatter->getBatchMode());
        self::assertFalse($googleCloudLoggingFormatter->isAppendingNewlines());

        $ig = new ReflectionProperty($googleCloudLoggingFormatter, 'ignoreEmptyContextAndExtra');

        self::assertTrue($ig->getValue($googleCloudLoggingFormatter));

        $st = new ReflectionProperty($googleCloudLoggingFormatter, 'includeStacktraces');

        self::assertTrue($st->getValue($googleCloudLoggingFormatter));
    }
}
