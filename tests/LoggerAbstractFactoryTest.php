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

namespace Mimmi20Test\MonologFactory;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\LoggerAbstractFactory;
use Mimmi20\MonologFactory\MonologPluginManager;
use Monolog\Logger;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function sprintf;

final class LoggerAbstractFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigException(): void
    {
        $requestedName            = Logger::class;
        $serviceNotFoundException = new ServiceNotFoundException();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException($serviceNotFoundException);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        try {
            $loggerAbstractFactory($container, $requestedName);

            self::fail('ServiceNotFoundException expected');
        } catch (ServiceNotFoundException $e) {
            self::assertSame(sprintf('Could not find service %s', 'config'), $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($serviceNotFoundException, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutManager(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage(
            '$pluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );
        $this->expectExceptionCode(1);

        $loggerAbstractFactory($container, $requestedName);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithManagerException(): void
    {
        $requestedName            = Logger::class;
        $config                   = [];
        $serviceNotFoundException = new ServiceNotFoundException();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $config, $serviceNotFoundException): array {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame('config', $id, (string) $invocation),
                        default => self::assertSame(
                            MonologPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $config,
                        default => throw $serviceNotFoundException,
                    };
                },
            );

        $loggerAbstractFactory = new LoggerAbstractFactory();

        try {
            $loggerAbstractFactory($container, $requestedName);

            self::fail('ServiceNotCreatedException expected');
        } catch (ServiceNotCreatedException $e) {
            self::assertSame(
                sprintf('Could not find service %s', MonologPluginManager::class),
                $e->getMessage(),
            );
            self::assertSame(0, $e->getCode());
            self::assertSame($serviceNotFoundException, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithLoggerException(): void
    {
        $requestedName            = Logger::class;
        $config                   = [];
        $logConfig                = [];
        $serviceNotFoundException = new ServiceNotFoundException();

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willThrowException($serviceNotFoundException);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        try {
            $loggerAbstractFactory($container, $requestedName);

            self::fail('ServiceNotCreatedException expected');
        } catch (ServiceNotCreatedException $e) {
            self::assertSame(sprintf('Could not find service %s', Logger::class), $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($serviceNotFoundException, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig(): void
    {
        $requestedName = Logger::class;
        $config        = null;
        $logConfig     = [];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [];
        $logConfig     = [];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig3(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => null];
        $logConfig     = [];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig4(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => []];
        $logConfig     = [];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig5(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => [$requestedName => null]];
        $logConfig     = [];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig6(): void
    {
        $requestedName = Logger::class;
        $logConfig     = [];
        $config        = ['log' => [$requestedName => $logConfig]];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig7(): void
    {
        $requestedName = Logger::class;
        $logConfig     = ['abc' => 'xyz'];
        $config        = ['log' => [$requestedName => $logConfig]];

        $logger = $this->createStub(Logger::class);

        $pluginManager = $this->createMock(AbstractPluginManager::class);
        $pluginManager->expects(self::never())
            ->method('has');
        $pluginManager->expects(self::never())
            ->method('get');
        $pluginManager->expects(self::once())
            ->method('build')
            ->with(Logger::class, $logConfig)
            ->willReturn($logger);

        $container = $this->createMock(ContainerInterface::class);
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

        $loggerAbstractFactory = new LoggerAbstractFactory();

        self::assertSame($logger, $loggerAbstractFactory($container, $requestedName));
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithConfigException(): void
    {
        $requestedName = Logger::class;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new ServiceNotFoundException());

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithoutConfig(): void
    {
        $requestedName = Logger::class;
        $config        = null;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithEmptyConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithConfig(): void
    {
        $requestedName = Logger::class;
        $config        = ['log' => null];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithConfig3(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [$requestedName => null],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testCanCreateWithConfig4(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $loggerAbstractFactory = new LoggerAbstractFactory();

        $cando = $loggerAbstractFactory->canCreate($container, $requestedName);

        self::assertTrue($cando);
    }
}
