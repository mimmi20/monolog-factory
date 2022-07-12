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

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\RedisHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class RedisHandlerFactoryTest extends TestCase
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

        $factory = new RedisHandlerFactory();

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

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithWrongClient(): void
    {
        $client = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithWrongClient2(): void
    {
        $client = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load client class for %s class', RedisHandler::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithError(): void
    {
        $client = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn(true);

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisHandler::class));

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithClient(): void
    {
        $clientName = 'abc';
        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');

        self::assertSame('', $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');

        self::assertSame(0, $cs->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithClient2(): void
    {
        $clientName = 'abc';
        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $capSize    = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');

        self::assertSame($capSize, $cs->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithClient3(): void
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');

        self::assertSame('', $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');

        self::assertSame(0, $cs->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithClient4(): void
    {
        $client  = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key     = 'test-key';
        $level   = LogLevel::ALERT;
        $bubble  = false;
        $capSize = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');

        self::assertSame($capSize, $cs->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithClient5(): void
    {
        $clientName = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn(true);

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', RedisHandler::class));

        $factory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
        $formatter = $this->getMockBuilder(LineFormatter::class)
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
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');

        self::assertSame($capSize, $cs->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $capSize    = 42;
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'processors' => $processors]);
    }
}
