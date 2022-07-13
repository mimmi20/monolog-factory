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

namespace Mimmi20Test\MonologFactory\Handler;

use Elastic\Elasticsearch\Client as V8Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elasticsearch\Client as V7Client;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\ClientPluginManager;
use Mimmi20\MonologFactory\Handler\ElasticsearchHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function class_exists;
use function date;
use function sprintf;

final class ElasticsearchHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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
        $this->expectExceptionMessage(sprintf('Could not load client class for %s class', ElasticsearchHandler::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
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
     * @throws InvalidArgumentException
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

        self::assertSame(Logger::DEBUG, $handler->getLevel());
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
     * @throws InvalidArgumentException
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

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
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
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function (string $var) use ($client, $clientClass): V7Client {
                    if ($var === $client) {
                        return $clientClass;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     * @throws InvalidArgumentException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => 'abc']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     * @throws InvalidArgumentException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     * @throws InvalidArgumentException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     * @throws InvalidArgumentException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter, 'dateFormat' => $dateFormat, 'indexNameFormat' => '{indexname}-{date}']);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     */
    public function testInvokeWithConfigWithArrayConfigForV7ClientButLoaderError(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $client = ['host' => 'localhost'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', ClientPluginManager::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigWithArrayConfigForV7ClientButLoaderError2(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $clientConfig = ['host' => 'localhost'];

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V7Client::class, $clientConfig)
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', V7Client::class));

        $factory($container, '', ['client' => $clientConfig]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigWithArrayConfigForV7ClientButLoaderError3(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $clientConfig = [];
        $client       = 4711;

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V7Client::class, $clientConfig)
            ->willReturn($client);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', V7Client::class));

        $handler = $factory($container, '', ['client' => $clientConfig]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
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
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigWithArrayConfigForV7ClientButLoaderError4(): void
    {
        if (!class_exists(V7Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V7');
        }

        $clientConfig = [];
        $client       = $this->getMockBuilder(V7Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V7Client::class, $clientConfig)
            ->willReturn($client);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientConfig]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
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
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigWithV8ClientClass(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $clientBuilder = new ClientBuilder();
        $client        = $clientBuilder->build();

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

        self::assertSame(Logger::DEBUG, $handler->getLevel());
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
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigWithV8ClassString(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client        = 'xyz';
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();
        $index         = 'test-index';
        $type          = 'test-type';

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

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     */
    public function testInvokeWithV8ClientAndConfigAndBoolFormatter(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client        = 'xyz';
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();
        $index         = 'test-index';
        $type          = 'test-type';
        $formatter     = true;

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
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeV8ClientAndWithConfigAndFormatter(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client        = 'xyz';
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();
        $index         = 'test-index';
        $type          = 'test-type';
        $formatter     = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function (string $var) use ($client, $clientClass): V8Client {
                    if ($var === $client) {
                        return $clientClass;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithV8ClientAndConfigAndFormatter2(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client        = 'xyz';
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();
        $index         = 'test-index';
        $type          = 'test-type';
        $formatter     = $this->getMockBuilder(ElasticsearchFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$client], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($clientClass, $monologFormatterPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'index' => $index, 'type' => $type, 'ignoreError' => true, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
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
     */
    public function testInvokeWithV8ClientAndConfigAndBoolProcessors(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client        = 'xyz';
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();
        $index         = 'test-index';
        $type          = 'test-type';
        $processors    = true;

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
     */
    public function testInvokeWithConfigWithArrayConfigForV8ClientButLoaderError(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $client = ['host' => 'localhost'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', ClientPluginManager::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigWithArrayConfigForV8ClientButLoaderError2(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $clientConfig = ['host' => 'localhost'];

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V8Client::class, $clientConfig)
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', V8Client::class));

        $factory($container, '', ['client' => $clientConfig]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigWithArrayConfigForV8ClientButLoaderError3(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $clientConfig = [];
        $client       = 4711;

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V8Client::class, $clientConfig)
            ->willReturn($client);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', V8Client::class));

        $factory($container, '', ['client' => $clientConfig]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigWithArrayConfigForV8ClientButLoaderError4(): void
    {
        if (!class_exists(V8Client::class)) {
            self::markTestSkipped('requires elasticsearch/elasticsearch V8');
        }

        $clientConfig  = [];
        $clientBuilder = new ClientBuilder();
        $clientClass   = $clientBuilder->build();

        $monologClientPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologClientPluginManager->expects(self::never())
            ->method('has');
        $monologClientPluginManager->expects(self::once())
            ->method('get')
            ->with(V8Client::class, $clientConfig)
            ->willReturn($clientClass);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(ClientPluginManager::class)
            ->willReturn($monologClientPluginManager);

        $factory = new ElasticsearchHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientConfig]);

        self::assertInstanceOf(ElasticsearchHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');

        self::assertSame($clientClass, $clientP->getValue($handler));

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
}
