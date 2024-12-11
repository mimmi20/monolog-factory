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

namespace Mimmi20Test\MonologFactory;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\LoggerAbstractFactory;
use Mimmi20\MonologFactory\MonologPluginManager;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function sprintf;

final class LoggerAbstractFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigException(): void
    {
        $requestedName = Logger::class;
        $exception     = new ServiceNotFoundException();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException($exception);

        $factory = new LoggerAbstractFactory();

        try {
            $factory($container, $requestedName);

            self::fail('ServiceNotFoundException expected');
        } catch (ServiceNotFoundException $e) {
            self::assertSame(sprintf('Could not find service %s', 'config'), $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutManager(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, null],
                ],
            );

        $factory = new LoggerAbstractFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage(
            '$pluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );
        $this->expectExceptionCode(1);

        $factory($container, $requestedName);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function testInvokeWithManagerException(): void
    {
        $requestedName = Logger::class;
        $config        = [];
        $exception     = new ServiceNotFoundException();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($config, $exception): array {
                    if ($var === 'config') {
                        return $config;
                    }

                    throw $exception;
                },
            );

        $factory = new LoggerAbstractFactory();

        try {
            $factory($container, $requestedName);

            self::fail('ServiceNotCreatedException expected');
        } catch (ServiceNotCreatedException $e) {
            self::assertSame(
                sprintf('Could not find service %s', MonologPluginManager::class),
                $e->getMessage(),
            );
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function testInvokeWithLoggerException(): void
    {
        $requestedName = Logger::class;
        $config        = [];
        $logConfig     = [];
        $exception     = new ServiceNotFoundException();

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willThrowException($exception);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        try {
            $factory($container, $requestedName);

            self::fail('ServiceNotCreatedException expected');
        } catch (ServiceNotCreatedException $e) {
            self::assertSame(sprintf('Could not find service %s', Logger::class), $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig(): void
    {
        $requestedName = Logger::class;
        $config        = null;
        $logConfig     = [];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [];
        $logConfig     = [];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig3(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => null];
        $logConfig     = [];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig4(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => []];
        $logConfig     = [];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig5(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => [$requestedName => null]];
        $logConfig     = [];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig6(): void
    {
        $requestedName = Logger::class;
        $logConfig     = [];
        $config        = ['log' => [$requestedName => $logConfig]];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutConfig7(): void
    {
        $requestedName = Logger::class;
        $logConfig     = ['abc' => 'xyz'];
        $config        = ['log' => [$requestedName => $logConfig]];

        $logger = $this->createMock(Logger::class);

        $pluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::once())
            ->method('get')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [MonologPluginManager::class, $pluginManager],
                ],
            );

        $factory = new LoggerAbstractFactory();

        self::assertSame($logger, $factory($container, $requestedName));
    }

    /** @throws Exception */
    public function testCanCreateWithConfigException(): void
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

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithoutConfig(): void
    {
        $requestedName = Logger::class;
        $config        = null;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithEmptyConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithConfig(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => null];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithConfig3(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [$requestedName => null],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /** @throws Exception */
    public function testCanCreateWithConfig4(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [],
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertTrue($cando);
    }
}
