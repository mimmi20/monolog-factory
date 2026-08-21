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

use Gelf\Message;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\GelfMessageFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\GelfMessageFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function gethostname;

final class GelfMessageFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter',
            );
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfMessageFormatterFactory = new GelfMessageFormatterFactory();

        $gelfMessageFormatter = $gelfMessageFormatterFactory($container, '');

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfMessageFormatter);
        self::assertSame('U.u', $gelfMessageFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $gelfMessageFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $gelfMessageFormatter->getMaxNormalizeItemCount(),
        );

        $s = new ReflectionProperty($gelfMessageFormatter, 'systemName');

        self::assertSame((string) gethostname(), $s->getValue($gelfMessageFormatter));

        $ep = new ReflectionProperty($gelfMessageFormatter, 'extraPrefix');

        self::assertSame('', $ep->getValue($gelfMessageFormatter));

        $cp = new ReflectionProperty($gelfMessageFormatter, 'contextPrefix');

        self::assertSame('ctxt_', $cp->getValue($gelfMessageFormatter));

        $ml = new ReflectionProperty($gelfMessageFormatter, 'maxLength');

        self::assertSame(32766, $ml->getValue($gelfMessageFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter',
            );
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfMessageFormatterFactory = new GelfMessageFormatterFactory();

        $gelfMessageFormatter = $gelfMessageFormatterFactory($container, '', []);

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfMessageFormatter);
        self::assertSame('U.u', $gelfMessageFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $gelfMessageFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $gelfMessageFormatter->getMaxNormalizeItemCount(),
        );

        $s = new ReflectionProperty($gelfMessageFormatter, 'systemName');

        self::assertSame((string) gethostname(), $s->getValue($gelfMessageFormatter));

        $ep = new ReflectionProperty($gelfMessageFormatter, 'extraPrefix');

        self::assertSame('', $ep->getValue($gelfMessageFormatter));

        $cp = new ReflectionProperty($gelfMessageFormatter, 'contextPrefix');

        self::assertSame('ctxt_', $cp->getValue($gelfMessageFormatter));

        $ml = new ReflectionProperty($gelfMessageFormatter, 'maxLength');

        self::assertSame(32766, $ml->getValue($gelfMessageFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter',
            );
        }

        $systemName            = 'abc';
        $extraPrefix           = '__xxx';
        $contextPrefix         = 'xyz';
        $maxLength             = 42;
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfMessageFormatterFactory = new GelfMessageFormatterFactory();

        $gelfMessageFormatter = $gelfMessageFormatterFactory($container, '', ['systemName' => $systemName, 'extraPrefix' => $extraPrefix, 'contextPrefix' => $contextPrefix, 'maxLength' => $maxLength, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfMessageFormatter);
        self::assertSame('U.u', $gelfMessageFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $gelfMessageFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $gelfMessageFormatter->getMaxNormalizeItemCount());

        $s = new ReflectionProperty($gelfMessageFormatter, 'systemName');

        self::assertSame($systemName, $s->getValue($gelfMessageFormatter));

        $ep = new ReflectionProperty($gelfMessageFormatter, 'extraPrefix');

        self::assertSame($extraPrefix, $ep->getValue($gelfMessageFormatter));

        $cp = new ReflectionProperty($gelfMessageFormatter, 'contextPrefix');

        self::assertSame($contextPrefix, $cp->getValue($gelfMessageFormatter));

        $ml = new ReflectionProperty($gelfMessageFormatter, 'maxLength');

        self::assertSame($maxLength, $ml->getValue($gelfMessageFormatter));
    }
}
