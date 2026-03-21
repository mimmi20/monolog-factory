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

use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManagerFactory;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ActivationStrategyPluginManagerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new ActivationStrategyPluginManagerFactory();

        $pluginManager = $factory($container, '');

        self::assertInstanceOf(ActivationStrategyPluginManager::class, $pluginManager);
    }
}
