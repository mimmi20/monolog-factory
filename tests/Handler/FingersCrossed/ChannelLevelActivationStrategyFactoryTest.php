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

namespace Mimmi20Test\MonologFactory\Handler\FingersCrossed;

use Mimmi20\MonologFactory\Handler\FingersCrossed\ChannelLevelActivationStrategyFactory;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Level;
use PHPUnit\Event\NoPreviousThrowableException;
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

        $channelLevelActivationStrategyFactory = new ChannelLevelActivationStrategyFactory();

        $channelLevelActivationStrategy = $channelLevelActivationStrategyFactory($container, '');

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $channelLevelActivationStrategy);

        $dal = new ReflectionProperty($channelLevelActivationStrategy, 'defaultActionLevel');

        self::assertSame(Level::Debug, $dal->getValue($channelLevelActivationStrategy));

        $ctal = new ReflectionProperty($channelLevelActivationStrategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($channelLevelActivationStrategy));
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

        $channelLevelActivationStrategyFactory = new ChannelLevelActivationStrategyFactory();

        $channelLevelActivationStrategy = $channelLevelActivationStrategyFactory($container, '', []);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $channelLevelActivationStrategy);

        $dal = new ReflectionProperty($channelLevelActivationStrategy, 'defaultActionLevel');

        self::assertSame(Level::Debug, $dal->getValue($channelLevelActivationStrategy));

        $ctal = new ReflectionProperty($channelLevelActivationStrategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($channelLevelActivationStrategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithWrongConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $channelLevelActivationStrategyFactory = new ChannelLevelActivationStrategyFactory();

        $channelLevelActivationStrategy = $channelLevelActivationStrategyFactory($container, '', ['defaultActionLevel' => LogLevel::ALERT, 'channelToActionLevel' => null]);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $channelLevelActivationStrategy);

        $dal = new ReflectionProperty($channelLevelActivationStrategy, 'defaultActionLevel');

        self::assertSame(Level::Alert, $dal->getValue($channelLevelActivationStrategy));

        $ctal = new ReflectionProperty($channelLevelActivationStrategy, 'channelToActionLevel');

        self::assertSame([], $ctal->getValue($channelLevelActivationStrategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $channelLevelActivationStrategyFactory = new ChannelLevelActivationStrategyFactory();

        $channelLevelActivationStrategy = $channelLevelActivationStrategyFactory($container, '', ['defaultActionLevel' => LogLevel::ALERT, 'channelToActionLevel' => ['abc' => LogLevel::CRITICAL, 'xyz' => LogLevel::WARNING]]);

        self::assertInstanceOf(ChannelLevelActivationStrategy::class, $channelLevelActivationStrategy);

        $dal = new ReflectionProperty($channelLevelActivationStrategy, 'defaultActionLevel');

        self::assertSame(Level::Alert, $dal->getValue($channelLevelActivationStrategy));

        $ctal = new ReflectionProperty($channelLevelActivationStrategy, 'channelToActionLevel');

        self::assertSame(
            ['abc' => Level::Critical, 'xyz' => Level::Warning],
            $ctal->getValue($channelLevelActivationStrategy),
        );
    }
}
