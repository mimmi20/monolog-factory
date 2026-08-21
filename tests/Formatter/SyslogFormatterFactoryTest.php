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
use Mimmi20\MonologFactory\Formatter\SyslogFormatterFactory;
use Monolog\Formatter\SyslogFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class SyslogFormatterFactoryTest extends TestCase
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

        $syslogFormatterFactory = new SyslogFormatterFactory();

        $syslogFormatter = $syslogFormatterFactory($container, '');

        self::assertInstanceOf(SyslogFormatter::class, $syslogFormatter);
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $syslogFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $syslogFormatter->getMaxNormalizeItemCount(),
        );

        $reflectionProperty = new ReflectionProperty($syslogFormatter, 'applicationName');

        self::assertSame('-', $reflectionProperty->getValue($syslogFormatter));
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

        $syslogFormatterFactory = new SyslogFormatterFactory();

        $syslogFormatter = $syslogFormatterFactory($container, '', []);

        self::assertInstanceOf(SyslogFormatter::class, $syslogFormatter);
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $syslogFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $syslogFormatter->getMaxNormalizeItemCount(),
        );

        $reflectionProperty = new ReflectionProperty($syslogFormatter, 'applicationName');

        self::assertSame('-', $reflectionProperty->getValue($syslogFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;
        $applicationName       = 'test-app';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogFormatterFactory = new SyslogFormatterFactory();

        $syslogFormatter = $syslogFormatterFactory($container, '', ['maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true, 'applicationName' => $applicationName]);

        self::assertInstanceOf(SyslogFormatter::class, $syslogFormatter);
        self::assertSame($maxNormalizeDepth, $syslogFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $syslogFormatter->getMaxNormalizeItemCount());

        $reflectionProperty = new ReflectionProperty($syslogFormatter, 'applicationName');

        self::assertSame($applicationName, $reflectionProperty->getValue($syslogFormatter));
    }
}
