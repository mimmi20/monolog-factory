<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Formatter;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\Formatter\LogstashFormatterFactory;
use Monolog\Formatter\LogstashFormatter;
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
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogstashFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutApplicationname(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogstashFormatterFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No applicationName provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithApplicationname(): void
    {
        $applicationName = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogstashFormatterFactory();

        $formatter = $factory($container, '', ['applicationName' => $applicationName]);

        self::assertInstanceOf(LogstashFormatter::class, $formatter);

        $appname = new ReflectionProperty($formatter, 'applicationName');

        self::assertSame($applicationName, $appname->getValue($formatter));

        $sys = new ReflectionProperty($formatter, 'systemName');

        self::assertSame((string) gethostname(), $sys->getValue($formatter));

        $ex = new ReflectionProperty($formatter, 'extraKey');

        self::assertSame('extra', $ex->getValue($formatter));

        $ctk = new ReflectionProperty($formatter, 'contextKey');

        self::assertSame('context', $ctk->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithApplicationname2(): void
    {
        $applicationName = 'abc';
        $systemName      = 'xyz';
        $extraKey        = 'xtra';
        $contextKey      = 'new-context';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogstashFormatterFactory();

        $formatter = $factory($container, '', ['applicationName' => $applicationName, 'systemName' => $systemName, 'extraPrefix' => $extraKey, 'contextPrefix' => $contextKey]);

        self::assertInstanceOf(LogstashFormatter::class, $formatter);

        $appname = new ReflectionProperty($formatter, 'applicationName');

        self::assertSame($applicationName, $appname->getValue($formatter));

        $sys = new ReflectionProperty($formatter, 'systemName');

        self::assertSame($systemName, $sys->getValue($formatter));

        $ex = new ReflectionProperty($formatter, 'extraKey');

        self::assertSame($extraKey, $ex->getValue($formatter));

        $ctk = new ReflectionProperty($formatter, 'contextKey');

        self::assertSame($contextKey, $ctk->getValue($formatter));
    }
}
