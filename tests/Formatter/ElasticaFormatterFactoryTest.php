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

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\ElasticaFormatterFactory;
use Mimmi20\MonologFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\ElasticaFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ElasticaFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
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

        $elasticaFormatterFactory = new ElasticaFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $elasticaFormatterFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutIndex(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $elasticaFormatterFactory = new ElasticaFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No index provided');

        $elasticaFormatterFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithIndex(): void
    {
        $index = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $elasticaFormatterFactory = new ElasticaFormatterFactory();

        $elasticaFormatter = $elasticaFormatterFactory($container, '', ['index' => $index]);

        self::assertInstanceOf(ElasticaFormatter::class, $elasticaFormatter);
        self::assertSame($index, $elasticaFormatter->getIndex());
        self::assertSame('', $elasticaFormatter->getType());
        self::assertSame('Y-m-d\TH:i:s.uP', $elasticaFormatter->getDateFormat());
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH,
            $elasticaFormatter->getMaxNormalizeDepth(),
        );
        self::assertSame(
            NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT,
            $elasticaFormatter->getMaxNormalizeItemCount(),
        );
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithIndexAndType(): void
    {
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;
        $index                 = 'abc';
        $type                  = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $elasticaFormatterFactory = new ElasticaFormatterFactory();

        $elasticaFormatter = $elasticaFormatterFactory($container, '', ['index' => $index, 'type' => $type, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(ElasticaFormatter::class, $elasticaFormatter);
        self::assertSame($index, $elasticaFormatter->getIndex());
        self::assertSame($type, $elasticaFormatter->getType());
        self::assertSame('Y-m-d\TH:i:s.uP', $elasticaFormatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $elasticaFormatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $elasticaFormatter->getMaxNormalizeItemCount());
    }
}
