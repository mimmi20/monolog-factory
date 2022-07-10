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

namespace Mimmi20Test\MonologFactory;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\LoggerAbstractFactory;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function sprintf;

final class LoggerAbstractFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvokeWithoutConfig(): void
    {
        $requestedName = Logger::class;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new ServiceNotFoundException());

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'config'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }
}
