<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Formatter;

use DateTimeInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\ElasticsearchFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\ElasticsearchFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ElasticsearchFormatterFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /** @throws Exception */
    public function testInvokeWithoutIndex(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No index provided');

        $factory($container, '', []);
    }

    /** @throws Exception */
    public function testInvokeWithIndex(): void
    {
        $index = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchFormatterFactory();

        $formatter = $factory($container, '', ['index' => $index]);

        self::assertInstanceOf(ElasticsearchFormatter::class, $formatter);
        self::assertSame($index, $formatter->getIndex());
        self::assertSame('', $formatter->getType());
        self::assertSame(DateTimeInterface::ISO8601, $formatter->getDateFormat());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH, $formatter->getMaxNormalizeDepth());
        self::assertSame(NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT, $formatter->getMaxNormalizeItemCount());
    }

    /** @throws Exception */
    public function testInvokeWithIndexAndType(): void
    {
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;
        $index                 = 'abc';
        $type                  = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchFormatterFactory();

        $formatter = $factory($container, '', ['index' => $index, 'type' => $type, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(ElasticsearchFormatter::class, $formatter);
        self::assertSame($index, $formatter->getIndex());
        self::assertSame($type, $formatter->getType());
        self::assertSame(DateTimeInterface::ISO8601, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());
    }
}
