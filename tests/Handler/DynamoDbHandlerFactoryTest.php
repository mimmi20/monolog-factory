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
use Aws\DynamoDb\DynamoDbClient;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\DynamoDbHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\DynamoDbHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class DynamoDbHandlerFactoryTest extends TestCase
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $dynamoDbHandlerFactory($container, '');
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $dynamoDbHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $clientName = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $dynamoDbHandlerFactory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $clientName = 'test-client';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willThrowException(new ServiceNotFoundException());

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', DynamoDbHandler::class),
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig3(): void
    {
        $clientName = 'test-client';
        $client     = $this->createStub(DynamoDbClient::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Debug, $dynamoDbHandler->getLevel());
        self::assertTrue($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame('', $tableP->getValue($dynamoDbHandler));

        self::assertInstanceOf(ScalarFormatter::class, $dynamoDbHandler->getFormatter());

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
    public function testInvokeWithConfig4(): void
    {
        $clientName = 'test-client';
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $clientName, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Alert, $dynamoDbHandler->getLevel());
        self::assertFalse($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame($table, $tableP->getValue($dynamoDbHandler));

        self::assertInstanceOf(ScalarFormatter::class, $dynamoDbHandler->getFormatter());

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
    public function testInvokeWithConfig5(): void
    {
        $client = $this->createStub(DynamoDbClient::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $client]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Debug, $dynamoDbHandler->getLevel());
        self::assertTrue($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame('', $tableP->getValue($dynamoDbHandler));

        self::assertInstanceOf(ScalarFormatter::class, $dynamoDbHandler->getFormatter());

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
    public function testInvokeWithConfig6(): void
    {
        $client = $this->createStub(DynamoDbClient::class);
        $table  = 'test-table';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Alert, $dynamoDbHandler->getLevel());
        self::assertFalse($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame($table, $tableP->getValue($dynamoDbHandler));

        self::assertInstanceOf(ScalarFormatter::class, $dynamoDbHandler->getFormatter());

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
    public function testInvokeWithConfig7(): void
    {
        $clientName = 'test-client';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn(value: true);

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', DynamoDbHandler::class));

        $dynamoDbHandlerFactory($container, '', ['client' => $clientName]);
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
        $client    = $this->createStub(DynamoDbClient::class);
        $table     = 'test-table';
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $client    = $this->createStub(DynamoDbClient::class);
        $table     = 'test-table';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $client    = $this->createStub(DynamoDbClient::class);
        $table     = 'test-table';
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Alert, $dynamoDbHandler->getLevel());
        self::assertFalse($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame($table, $tableP->getValue($dynamoDbHandler));

        self::assertSame($formatter, $dynamoDbHandler->getFormatter());

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
        $client    = $this->createStub(DynamoDbClient::class);
        $table     = 'test-table';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $dynamoDbHandler = $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(DynamoDbHandler::class, $dynamoDbHandler);

        self::assertSame(Level::Alert, $dynamoDbHandler->getLevel());
        self::assertFalse($dynamoDbHandler->getBubble());

        $clientP = new ReflectionProperty($dynamoDbHandler, 'client');

        self::assertSame($client, $clientP->getValue($dynamoDbHandler));

        $tableP = new ReflectionProperty($dynamoDbHandler, 'table');

        self::assertSame($table, $tableP->getValue($dynamoDbHandler));

        $proc = new ReflectionProperty($dynamoDbHandler, 'processors');

        $processors = $proc->getValue($dynamoDbHandler);

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
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $client     = $this->createStub(DynamoDbClient::class);
        $table      = 'test-table';
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

        $dynamoDbHandlerFactory = new DynamoDbHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $dynamoDbHandlerFactory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
