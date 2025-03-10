<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2025, Thomas Mueller <mimmi20@live.de>
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
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Exception;
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

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithWrongClient(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithWrongClientString(): void
    {
        $client = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not load client class for %s class', ElasticsearchHandler::class),
        );

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigError(): void
    {
        $client = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(true);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', ElasticsearchHandler::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithV7ClientClass(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($client, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame('monolog', $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertFalse($optionsArray['ignore_error']);

        self::assertInstanceOf(ElasticsearchFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithV7ClassString(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertInstanceOf(ElasticsearchFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndBoolFormatter(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeV7ClientAndWithConfigAndFormatter(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $matcher = self::exactly(2);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $client, $clientClass): V7Client {
                    $invocation = $matcher->numberOfInvocations();

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

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_MONTH;
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => 'abc']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter4(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_MONTH;
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter5(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_YEAR;
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndFormatter6(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $dateFormat  = ElasticsearchHandlerFactory::INDEX_PER_DAY;
        $formatter   = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index . '-' . date($dateFormat), $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndBoolProcessors(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $index       = 'test-index';
        $type        = 'test-type';
        $processors  = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndProcessors2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithV7ClientAndConfigAndProcessors3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $processor1 = $this->getMockBuilder(GitProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor2 = $this->getMockBuilder(HostnameProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Level::Alert->value, $handler->getLevel()->value);
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

        $optionsP = new ReflectionProperty($handler, 'options');

        $optionsArray = $optionsP->getValue($handler);

        self::assertIsArray($optionsArray);

        self::assertSame($index, $optionsArray['index']);
        self::assertSame('_doc', $optionsArray['type']);
        self::assertTrue($optionsArray['ignore_error']);

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

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
     */
    public function testInvokeWithV7ClientAndConfigAndProcessors4(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client      = 'xyz';
        $clientClass = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $matcher = self::exactly(2);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $client, $clientClass) {
                    $invocation = $matcher->numberOfInvocations();

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

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
