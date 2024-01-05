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

namespace Mimmi20Test\MonologFactory\Handler\FingersCrossed;

use Mimmi20\MonologFactory\Handler\FingersCrossed\ErrorLevelActivationStrategyFactory;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

final class ErrorLevelActivationStrategyFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new ErrorLevelActivationStrategyFactory();

        $strategy = $factory($container, '');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $strategy);

        $al = new ReflectionProperty($strategy, 'actionLevel');

        self::assertSame(Level::Debug, $al->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ErrorLevelActivationStrategyFactory();

        $strategy = $factory($container, '', []);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $strategy);

        $al = new ReflectionProperty($strategy, 'actionLevel');

        self::assertSame(Level::Debug, $al->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ErrorLevelActivationStrategyFactory();

        $strategy = $factory($container, '', ['actionLevel' => LogLevel::ALERT]);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $strategy);

        $al = new ReflectionProperty($strategy, 'actionLevel');

        self::assertSame(Level::Alert, $al->getValue($strategy));
    }
}
