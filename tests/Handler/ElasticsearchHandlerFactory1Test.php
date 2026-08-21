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

use Elasticsearch\Client as V7Client;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function date;
use function sprintf;

#[IgnoreDeprecations]
final class ElasticsearchHandlerFactory1Test extends TestCase
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

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $elasticsearchHandlerFactory($container, '');
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

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $elasticsearchHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigWithWrongClient(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $elasticsearchHandlerFactory($container, '', ['client' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigWithWrongClientString(): void
    {
        $client = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', ElasticsearchHandler::class),
        );

        $elasticsearchHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigError(): void
    {
        $client = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(value: true);

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', ElasticsearchHandler::class));

        $elasticsearchHandlerFactory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigWithV7ClientClass(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client = $this->createStub(V7Client::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client]);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Debug, $elasticsearchHandler->getLevel());
        self::assertTrue($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($client, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame('monolog', $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertFalse($optionsArray['ignore_error']);

        self::assertInstanceOf(ElasticsearchFormatter::class, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithConfigWithV7ClassString(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertInstanceOf(ElasticsearchFormatter::class, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndBoolFormatter(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeV7ClientAndWithConfigAndFormatter(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $client, $clientClass): V7Client {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($client, $id, (string) $invocation),
                        default => self::assertSame(
                            MonologFormatterPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $clientClass,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndFormatter3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_MONTH;
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => 'abc']);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndFormatter4(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_MONTH;
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndFormatter5(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_YEAR;
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndFormatter6(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_DAY;
        $formatter   = $this->createStub(ElasticsearchFormatter::class);

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert, $elasticsearchHandler->getLevel());
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $elasticsearchHandler->getFormatter());

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndBoolProcessors(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $processors  = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithV7ClientAndConfigAndProcessors2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $processors  = [
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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithV7ClientAndConfigAndProcessors3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $processor3  = static fn (array $record): array => $record;
        $processors  = [
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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$client, $clientClass],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $elasticsearchHandler = $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(ElasticsearchHandler::class, $elasticsearchHandler);

        self::assertSame(Level::Alert->value, $elasticsearchHandler->getLevel()->value);
        self::assertFalse($elasticsearchHandler->getBubble());

        $clientP = new ReflectionProperty($elasticsearchHandler, 'client');

        self::assertSame($clientClass, $clientP->getValue($elasticsearchHandler));

        $optionsP = new ReflectionProperty($elasticsearchHandler, 'options');

        $optionsArray = $optionsP->getValue($elasticsearchHandler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        $proc = new ReflectionProperty($elasticsearchHandler, 'processors');

        $processors = $proc->getValue($elasticsearchHandler);

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
    public function testInvokeWithV7ClientAndConfigAndProcessors4(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->createStub(V7Client::class);
        $index       = 'test-index';
        $type        = 'test-type';
        $processor3  = static fn (array $record): array => $record;
        $processors  = [
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

        $monologProcessorPluginManager = $this->createMock(AbstractPluginManager::class);
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::never())
            ->method('build');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $invokedCount = self::exactly(2);
        $container->expects($invokedCount)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($invokedCount, $client, $clientClass): Stub {
                    $invocation = $invokedCount->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($client, $id, (string) $invocation),
                        default => self::assertSame(
                            MonologProcessorPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $clientClass,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $elasticsearchHandlerFactory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $elasticsearchHandlerFactory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
