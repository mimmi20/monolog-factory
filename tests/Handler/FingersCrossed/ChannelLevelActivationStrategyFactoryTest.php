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

use Mimmi20\MonologFactory\Handler\FingersCrossed\ChannelLevelActivationStrategyFactory;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

final class ChannelLevelActivationStrategyFactoryTest extends TestCase
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

        $factory = new ChannelLevelActivationStrategyFactory();

        $strategy = $factory($container, '');

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $strategy);

        $dal = new ReflectionProperty($strategy, 'defaultActionLevel');

        self::assertSame(Level::Debug, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($strategy));
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

        $factory = new ChannelLevelActivationStrategyFactory();

        $strategy = $factory($container, '', []);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $strategy);

        $dal = new ReflectionProperty($strategy, 'defaultActionLevel');

        self::assertSame(Level::Debug, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithWrongConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ChannelLevelActivationStrategyFactory();

        $strategy = $factory($container, '', ['defaultActionLevel' => LogLevel::ALERT, 'channelToActionLevel' => null]);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $strategy);

        $dal = new ReflectionProperty($strategy, 'defaultActionLevel');

        self::assertSame(Level::Alert, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($strategy));
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

        $factory = new ChannelLevelActivationStrategyFactory();

        $strategy = $factory($container, '', ['defaultActionLevel' => LogLevel::ALERT, 'channelToActionLevel' => ['abc' => LogLevel::CRITICAL, 'xyz' => LogLevel::WARNING]]);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $strategy);

        $dal = new ReflectionProperty($strategy, 'defaultActionLevel');

        self::assertSame(Level::Alert, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');

        self::assertSame(
            ['abc' => Level::Critical, 'xyz' => Level::Warning],
            $ctal->getValue($strategy),
        );
    }
}
