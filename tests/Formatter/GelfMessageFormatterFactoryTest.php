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

use Gelf\Message;
use Mimmi20\MonologFactory\Formatter\GelfMessageFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\GelfMessageFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function class_exists;
use function gethostname;

final class GelfMessageFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithoutConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped('Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);
        self::assertSame('U.u', $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $s = new ReflectionProperty($formatter, 'systemName');

        self::assertSame((string) gethostname(), $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');

        self::assertSame('', $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');

        self::assertSame('ctxt_', $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');

        self::assertSame(32766, $ml->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped('Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter');
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);
        self::assertSame('U.u', $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());

        $s = new ReflectionProperty($formatter, 'systemName');

        self::assertSame((string) gethostname(), $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');

        self::assertSame('', $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');

        self::assertSame('ctxt_', $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');

        self::assertSame(32766, $ml->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        if (!class_exists(Message::class)) {
            self::markTestSkipped('Composer package graylog2/gelf-php is required to use Monolog\'s GelfMessageFormatter');
        }

        $systemName            = 'abc';
        $extraPrefix           = '__xxx';
        $contextPrefix         = 'xyz';
        $maxLength             = 42;
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '', ['systemName' => $systemName, 'extraPrefix' => $extraPrefix, 'contextPrefix' => $contextPrefix, 'maxLength' => $maxLength, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);
        self::assertSame('U.u', $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());

        $s = new ReflectionProperty($formatter, 'systemName');

        self::assertSame($systemName, $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');

        self::assertSame($extraPrefix, $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');

        self::assertSame($contextPrefix, $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');

        self::assertSame($maxLength, $ml->getValue($formatter));
    }
}
