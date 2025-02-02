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

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\BufferHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class BufferHandlerFactory1Test extends TestCase
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

        $factory = new BufferHandlerFactory();

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

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfigWithLoaderError(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotCreatedException());

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfigWithLoaderError2(): void
    {
        $type = 'abc';

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(BufferHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');

        self::assertSame(0, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');

        self::assertTrue($fof->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithHandlerConfig2(): void
    {
        $type           = 'abc';
        $bufferLimit    = 42;
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false]);

        self::assertInstanceOf(BufferHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');

        self::assertSame($bufferLimit, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');

        self::assertFalse($fof->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $formatter   = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $formatter   = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $formatter   = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $matcher = self::exactly(2);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $monologHandlerPluginManager): AbstractPluginManager {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            MonologFormatterPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $monologHandlerPluginManager,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type           = 'abc';
        $bufferLimit    = 42;
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new BufferHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false, 'formatter' => $formatterClass]);

        self::assertInstanceOf(BufferHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');

        self::assertSame($bufferLimit, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');

        self::assertFalse($fof->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $formatter   = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $matcher = self::exactly(2);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $monologHandlerPluginManager): AbstractPluginManager {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            MonologHandlerPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            MonologFormatterPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $monologHandlerPluginManager,
                        default => throw new ServiceNotFoundException(),
                    };
                },
            );

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $type           = 'abc';
        $bufferLimit    = 42;
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['formatter' => $formatterClass])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new BufferHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatterClass]], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false]);

        self::assertInstanceOf(BufferHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');

        self::assertSame($bufferLimit, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');

        self::assertFalse($fof->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithConfigAndFormatter5(): void
    {
        $type           = 'abc';
        $bufferLimit    = 42;
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $factory = new BufferHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false, 'formatter' => $formatterClass]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $processors  = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type        = 'abc';
        $bufferLimit = 42;
        $processors  = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');
        $monologHandlerPluginManager->expects(self::once())
            ->method('build')
            ->with($type, ['processors' => $processors])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new BufferHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'bufferLimit' => $bufferLimit, 'level' => LogLevel::ALERT, 'bubble' => false, 'flushOnOverflow' => false]);
    }
}
