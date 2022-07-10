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

namespace Mimmi20Test\MonologFactory\Handler\FingersCrossed;

use Mimmi20\MonologFactory\Handler\FingersCrossed\ChannelLevelActivationStrategyFactory;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ChannelLevelActivationStrategyFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dal->setAccessible(true);

        self::assertSame(Logger::DEBUG, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');
        $ctal->setAccessible(true);

        self::assertSame([], $ctal->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dal->setAccessible(true);

        self::assertSame(Logger::DEBUG, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');
        $ctal->setAccessible(true);

        self::assertSame([], $ctal->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dal->setAccessible(true);

        self::assertSame(Logger::ALERT, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');
        $ctal->setAccessible(true);

        self::assertSame([], $ctal->getValue($strategy));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
        $dal->setAccessible(true);

        self::assertSame(Logger::ALERT, $dal->getValue($strategy));

        $ctal = new ReflectionProperty($strategy, 'channelToActionLevel');
        $ctal->setAccessible(true);

        self::assertSame(['abc' => Logger::CRITICAL, 'xyz' => Logger::WARNING], $ctal->getValue($strategy));
    }
}
