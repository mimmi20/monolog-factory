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

namespace Mimmi20Test\MonologFactory\Handler;

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\RedisPubSubHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RedisPubSubHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class RedisPubSubHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
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

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $redisPubSubHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $redisPubSubHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithWrongClient(): void
    {
        $client = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $redisPubSubHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithWrongClient2(): void
    {
        $client = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(value: true);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisPubSubHandler::class));

        $redisPubSubHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithError(): void
    {
        $client = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', RedisPubSubHandler::class),
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithClient(): void
    {
        $clientName = 'abc';
        $client     = $this->createStub(Client::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Debug, $redisPubSubHandler->getLevel());
        self::assertTrue($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame('', $ck->getValue($redisPubSubHandler));

        self::assertInstanceOf(LineFormatter::class, $redisPubSubHandler->getFormatter());

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithClient2(): void
    {
        $clientName = 'abc';
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $clientName, 'key' => $key, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Alert, $redisPubSubHandler->getLevel());
        self::assertFalse($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame($key, $ck->getValue($redisPubSubHandler));

        self::assertInstanceOf(LineFormatter::class, $redisPubSubHandler->getFormatter());

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithClient3(): void
    {
        $client = $this->createStub(Client::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $client]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Debug, $redisPubSubHandler->getLevel());
        self::assertTrue($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame('', $ck->getValue($redisPubSubHandler));

        self::assertInstanceOf(LineFormatter::class, $redisPubSubHandler->getFormatter());

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithClient4(): void
    {
        $client = $this->createStub(Client::class);
        $key    = 'test-key';
        $level  = LogLevel::ALERT;
        $bubble = false;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Alert, $redisPubSubHandler->getLevel());
        self::assertFalse($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame($key, $ck->getValue($redisPubSubHandler));

        self::assertInstanceOf(LineFormatter::class, $redisPubSubHandler->getFormatter());

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithClient5(): void
    {
        $clientName = 'abc';
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn(value: true);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisPubSubHandler::class));

        $redisPubSubHandlerFactory($container, '', ['client' => $clientName, 'key' => $key, 'level' => $level, 'bubble' => $bubble]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $client    = $this->createStub(Client::class);
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $client    = $this->createStub(Client::class);
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $client    = $this->createStub(Client::class);
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $monologFormatterPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Alert, $redisPubSubHandler->getLevel());
        self::assertFalse($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame($key, $ck->getValue($redisPubSubHandler));

        self::assertSame($formatter, $redisPubSubHandler->getFormatter());

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $client    = $this->createStub(Client::class);
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            static fn (array $record): array => $record,
        ];

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $processor1 = $this->createStub(GitProcessor::class);

        $processor2 = $this->createStub(HostnameProcessor::class);

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('build')
            ->willReturnMap(
                [
                    ['abc', [], $processor1],
                    ['xyz', ['efg' => 'ijk'], $processor2],
                ],
            );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $redisPubSubHandler = $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);

        self::assertInstanceOf(RedisPubSubHandler::class, $redisPubSubHandler);

        self::assertSame(Level::Alert, $redisPubSubHandler->getLevel());
        self::assertFalse($redisPubSubHandler->getBubble());

        $rc = new ReflectionProperty($redisPubSubHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisPubSubHandler));

        $ck = new ReflectionProperty($redisPubSubHandler, 'channelKey');

        self::assertSame($key, $ck->getValue($redisPubSubHandler));

        $proc = new ReflectionProperty($redisPubSubHandler, 'processors');

        $processors = $proc->getValue($redisPubSubHandler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $client     = $this->createStub(Client::class);
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'options' => ['efg' => 'ijk'],
                'type' => 'xyz',
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(value: null);

        $redisPubSubHandlerFactory = new RedisPubSubHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $redisPubSubHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
