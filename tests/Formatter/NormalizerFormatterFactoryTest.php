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

use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use const JSON_PRETTY_PRINT;

final class NormalizerFormatterFactoryTest extends TestCase
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

        $normalizerFormatterFactory = new NormalizerFormatterFactory();

        $normalizerFormatter = $normalizerFormatterFactory($container, '');

        self::assertInstanceOf(NormalizerFormatter::class, $normalizerFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $normalizerFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $normalizerFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $normalizerFormatter->getMaxNormalizeItemCount(),
        );

        $reflectionProperty = new ReflectionProperty($normalizerFormatter, 'jsonEncodeOptions');

        $jsonEncodeOptions = $reflectionProperty->getValue($normalizerFormatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
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

        $normalizerFormatterFactory = new NormalizerFormatterFactory();

        $normalizerFormatter = $normalizerFormatterFactory($container, '', []);

        self::assertInstanceOf(NormalizerFormatter::class, $normalizerFormatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $normalizerFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $normalizerFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $normalizerFormatter->getMaxNormalizeItemCount(),
        );

        $reflectionProperty = new ReflectionProperty($normalizerFormatter, 'jsonEncodeOptions');

        $jsonEncodeOptions = $reflectionProperty->getValue($normalizerFormatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $normalizerFormatterFactory = new NormalizerFormatterFactory();

        $normalizerFormatter = $normalizerFormatterFactory($container, '', ['dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(NormalizerFormatter::class, $normalizerFormatter);
        self::assertSame($dateFormat, $normalizerFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $normalizerFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $normalizerFormatter->getMaxNormalizeItemCount());

        $reflectionProperty = new ReflectionProperty($normalizerFormatter, 'jsonEncodeOptions');

        $jsonEncodeOptions = $reflectionProperty->getValue($normalizerFormatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
    }
}
