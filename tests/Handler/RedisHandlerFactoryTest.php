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
use Mimmi20\MonologFactory\Handler\RedisHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

#[IgnoreDeprecations]
final class RedisHandlerFactoryTest extends TestCase
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $redisHandlerFactory($container, '');
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $redisHandlerFactory($container, '', []);
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $redisHandlerFactory($container, '', ['client' => $client]);
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
            ->willThrowException(new ServiceNotFoundException());

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', RedisHandler::class),
        );

        $redisHandlerFactory($container, '', ['client' => $client]);
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
            ->willReturn(value: true);

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisHandler::class));

        $redisHandlerFactory($container, '', ['client' => $client]);
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Debug, $redisHandler->getLevel());
        self::assertTrue($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame('', $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame(0, $cs->getValue($redisHandler));

        self::assertInstanceOf(LineFormatter::class, $redisHandler->getFormatter());

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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
        $capSize    = 42;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $clientName, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Alert, $redisHandler->getLevel());
        self::assertFalse($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame($key, $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame($capSize, $cs->getValue($redisHandler));

        self::assertInstanceOf(LineFormatter::class, $redisHandler->getFormatter());

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $client]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Debug, $redisHandler->getLevel());
        self::assertTrue($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame('', $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame(0, $cs->getValue($redisHandler));

        self::assertInstanceOf(LineFormatter::class, $redisHandler->getFormatter());

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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
        $client  = $this->createStub(Client::class);
        $key     = 'test-key';
        $level   = LogLevel::ALERT;
        $bubble  = false;
        $capSize = 42;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Alert, $redisHandler->getLevel());
        self::assertFalse($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame($key, $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame($capSize, $cs->getValue($redisHandler));

        self::assertInstanceOf(LineFormatter::class, $redisHandler->getFormatter());

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn(value: true);

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisHandler::class));

        $redisHandlerFactory($container, '', ['client' => $clientName]);
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
        $capSize   = 42;
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
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
        $capSize   = 42;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
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
        $capSize   = 42;
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Alert, $redisHandler->getLevel());
        self::assertFalse($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame($key, $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame($capSize, $cs->getValue($redisHandler));

        self::assertSame($formatter, $redisHandler->getFormatter());

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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
        $capSize   = 42;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
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
        $capSize    = 42;
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);
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
        $capSize    = 42;
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);
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
        $capSize    = 42;
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $redisHandler = $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);

        self::assertInstanceOf(RedisHandler::class, $redisHandler);

        self::assertSame(Level::Alert, $redisHandler->getLevel());
        self::assertFalse($redisHandler->getBubble());

        $rc = new ReflectionProperty($redisHandler, 'redisClient');

        self::assertSame($client, $rc->getValue($redisHandler));

        $ck = new ReflectionProperty($redisHandler, 'redisKey');

        self::assertSame($key, $ck->getValue($redisHandler));

        $cs = new ReflectionProperty($redisHandler, 'capSize');

        self::assertSame($capSize, $cs->getValue($redisHandler));

        $proc = new ReflectionProperty($redisHandler, 'processors');

        $processors = $proc->getValue($redisHandler);

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
        $capSize    = 42;
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);
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
        $capSize    = 42;
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

        $redisHandlerFactory = new RedisHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $redisHandlerFactory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);
    }
}
