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

use AMQPExchange;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\AmqpHandlerFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AmqpHandler;
use Monolog\Level;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function sprintf;

final class AmqpHandlerFactoryTest extends TestCase
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

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $amqpHandlerFactory($container, '');
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

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required exchange class');

        $amqpHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithWrongExchange(): void
    {
        $exchange = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required exchange class');

        $amqpHandlerFactory($container, '', ['exchange' => $exchange]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithWrongExchange2(): void
    {
        $exchange = 'test';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willThrowException(new ServiceNotFoundException());

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load exchange class');

        $amqpHandlerFactory($container, '', ['exchange' => $exchange]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->createStub(AMQPExchange::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchange]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Debug, $amqpHandler->getLevel());
        self::assertTrue($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
    public function testInvokeWithConfig2(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->createStub(AMQPExchange::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchange, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Alert, $amqpHandler->getLevel());
        self::assertFalse($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
    public function testInvokeWithConfig3(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchangeClass = $this->createStub(AMQPExchange::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchangeClass]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Debug, $amqpHandler->getLevel());
        self::assertTrue($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchangeClass = $this->createStub(AMQPExchange::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchangeClass, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Alert, $amqpHandler->getLevel());
        self::assertFalse($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->createStub(AMQPChannel::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchange]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Debug, $amqpHandler->getLevel());
        self::assertTrue($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        $ecn = new ReflectionProperty($amqpHandler, 'exchangeName');

        self::assertSame('log', $ecn->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchange      = 'test';
        $exchangeName  = 'exchange-name-test';
        $exchangeClass = $this->createStub(AMQPChannel::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchange, 'exchangeName' => $exchangeName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Alert, $amqpHandler->getLevel());
        self::assertFalse($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        $ecn = new ReflectionProperty($amqpHandler, 'exchangeName');

        self::assertSame($exchangeName, $ecn->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
    public function testInvokeWithConfig7(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchangeClass = $this->createStub(AMQPChannel::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchangeClass]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Debug, $amqpHandler->getLevel());
        self::assertTrue($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        $ecn = new ReflectionProperty($amqpHandler, 'exchangeName');

        self::assertSame('log', $ecn->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
    public function testInvokeWithConfig8(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchangeName  = 'exchange-name-test';
        $exchangeClass = $this->createStub(AMQPChannel::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $amqpHandler = $amqpHandlerFactory($container, '', ['exchange' => $exchangeClass, 'exchangeName' => $exchangeName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $amqpHandler);

        self::assertSame(Level::Alert, $amqpHandler->getLevel());
        self::assertFalse($amqpHandler->getBubble());

        $ec = new ReflectionProperty($amqpHandler, 'exchange');

        self::assertSame($exchangeClass, $ec->getValue($amqpHandler));

        $ecn = new ReflectionProperty($amqpHandler, 'exchangeName');

        self::assertSame($exchangeName, $ecn->getValue($amqpHandler));

        self::assertInstanceOf(JsonFormatter::class, $amqpHandler->getFormatter());

        $proc = new ReflectionProperty($amqpHandler, 'processors');

        $processors = $proc->getValue($amqpHandler);

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
    public function testInvokeWithConfig9(): void
    {
        $exchange = 'test';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn(value: true);

        $amqpHandlerFactory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', AmqpHandler::class));

        $amqpHandlerFactory($container, '', ['exchange' => $exchange, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
