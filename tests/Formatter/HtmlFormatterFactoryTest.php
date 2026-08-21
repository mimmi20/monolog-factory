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

use Mimmi20\MonologFactory\Formatter\HtmlFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class HtmlFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $htmlFormatterFactory = new HtmlFormatterFactory();

        $htmlFormatter = $htmlFormatterFactory($container, '');

        self::assertInstanceOf(HtmlFormatter::class, $htmlFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $htmlFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $htmlFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $htmlFormatter->getMaxNormalizeItemCount(),
        );
    }

    /**
     * @throws Exception
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

        $htmlFormatterFactory = new HtmlFormatterFactory();

        $htmlFormatter = $htmlFormatterFactory($container, '', []);

        self::assertInstanceOf(HtmlFormatter::class, $htmlFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $htmlFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $htmlFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $htmlFormatter->getMaxNormalizeItemCount(),
        );
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $dateFormat            = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $htmlFormatterFactory = new HtmlFormatterFactory();

        $htmlFormatter = $htmlFormatterFactory($container, '', ['dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(HtmlFormatter::class, $htmlFormatter);
        self::assertSame($dateFormat, $htmlFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $htmlFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $htmlFormatter->getMaxNormalizeItemCount());
    }
}
