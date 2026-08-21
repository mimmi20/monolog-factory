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
use Mimmi20\MonologFactory\Formatter\LogstashFormatterFactory;
use Monolog\Formatter\LogstashFormatter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

use function gethostname;

final class LogstashFormatterFactoryTest extends TestCase
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

        $logstashFormatterFactory = new LogstashFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $logstashFormatterFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutApplicationname(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logstashFormatterFactory = new LogstashFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No applicationName provided');

        $logstashFormatterFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithApplicationname(): void
    {
        $applicationName = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logstashFormatterFactory = new LogstashFormatterFactory();

        $logstashFormatter = $logstashFormatterFactory($container, '', ['applicationName' => $applicationName]);

        self::assertInstanceOf(LogstashFormatter::class, $logstashFormatter);

        $appname = new ReflectionProperty($logstashFormatter, 'applicationName');

        self::assertSame($applicationName, $appname->getValue($logstashFormatter));

        $sys = new ReflectionProperty($logstashFormatter, 'systemName');

        self::assertSame((string) gethostname(), $sys->getValue($logstashFormatter));

        $ex = new ReflectionProperty($logstashFormatter, 'extraKey');

        self::assertSame('extra', $ex->getValue($logstashFormatter));

        $ctk = new ReflectionProperty($logstashFormatter, 'contextKey');

        self::assertSame('context', $ctk->getValue($logstashFormatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithApplicationname2(): void
    {
        $applicationName = 'abc';
        $systemName      = 'xyz';
        $extraKey        = 'xtra';
        $contextKey      = 'new-context';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $logstashFormatterFactory = new LogstashFormatterFactory();

        $logstashFormatter = $logstashFormatterFactory($container, '', ['applicationName' => $applicationName, 'systemName' => $systemName, 'extraPrefix' => $extraKey, 'contextPrefix' => $contextKey]);

        self::assertInstanceOf(LogstashFormatter::class, $logstashFormatter);

        $appname = new ReflectionProperty($logstashFormatter, 'applicationName');

        self::assertSame($applicationName, $appname->getValue($logstashFormatter));

        $sys = new ReflectionProperty($logstashFormatter, 'systemName');

        self::assertSame($systemName, $sys->getValue($logstashFormatter));

        $ex = new ReflectionProperty($logstashFormatter, 'extraKey');

        self::assertSame($extraKey, $ex->getValue($logstashFormatter));

        $ctk = new ReflectionProperty($logstashFormatter, 'contextKey');

        self::assertSame($contextKey, $ctk->getValue($logstashFormatter));
    }
}
