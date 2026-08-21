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
use Mimmi20\MonologFactory\Handler\MandrillHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MandrillHandler;
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
use Swift_Message;

use function class_exists;
use function sprintf;

final class MandrillHandlerFactoryTest extends TestCase
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $mandrillHandlerFactory($container, '');
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No apiKey provided');

        $mandrillHandlerFactory($container, '', []);
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
        $apiKey = 'test-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No message service name or callback provided');

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey]);
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
        $apiKey  = 'test-key';
        $message = 'test-message';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($message)
            ->willReturn(value: false);
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Message service found');

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig3(): void
    {
        $apiKey  = 'test-key';
        $message = 'test-message';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($message)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($message)
            ->willThrowException(new ServiceNotFoundException());

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load service %s', $message));

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey      = 'test-key';
        $messageName = 'test-message';
        $message     = $this->createStub(Swift_Message::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($messageName)
            ->willReturn($message);

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $messageName]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Debug, $mandrillHandler->getLevel());
        self::assertTrue($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($message, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey      = 'test-key';
        $messageName = 'test-message';
        $message     = $this->createStub(Swift_Message::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($messageName)
            ->willReturn($message);

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $messageName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Alert, $mandrillHandler->getLevel());
        self::assertFalse($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($message, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey  = 'test-key';
        $message = $this->createStub(Swift_Message::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Debug, $mandrillHandler->getLevel());
        self::assertTrue($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($message, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey  = 'test-key';
        $message = $this->createStub(Swift_Message::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Alert, $mandrillHandler->getLevel());
        self::assertFalse($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($message, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
    public function testInvokeWithConfig9(): void
    {
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Debug, $mandrillHandler->getLevel());
        self::assertTrue($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($messageClass, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
    public function testInvokeWithConfig10(): void
    {
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Alert, $mandrillHandler->getLevel());
        self::assertFalse($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($messageClass, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertInstanceOf(HtmlFormatter::class, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
    public function testInvokeWithConfig11(): void
    {
        $apiKey  = 'test-key';
        $message = static fn (): null => null;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', MandrillHandler::class),
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = $this->createStub(LineFormatter::class);

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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Alert, $mandrillHandler->getLevel());
        self::assertFalse($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($messageClass, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        self::assertSame($formatter, $mandrillHandler->getFormatter());

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $processors   = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $processors   = [
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $processor3   = static fn (array $record): array => $record;
        $processors   = [
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $mandrillHandler = $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(MandrillHandler::class, $mandrillHandler);

        self::assertSame(Level::Alert, $mandrillHandler->getLevel());
        self::assertFalse($mandrillHandler->getBubble());

        $messageP = new ReflectionProperty($mandrillHandler, 'message');

        self::assertSame($messageClass, $messageP->getValue($mandrillHandler));

        $ak = new ReflectionProperty($mandrillHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($mandrillHandler));

        $proc = new ReflectionProperty($mandrillHandler, 'processors');

        $processors = $proc->getValue($mandrillHandler);

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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $processor3   = static fn (array $record): array => $record;
        $processors   = [
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!class_exists(Swift_Message::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Swift_Message::class));
        }

        $apiKey       = 'test-key';
        $messageClass = $this->createStub(Swift_Message::class);
        $message      = static fn (): Swift_Message => $messageClass;
        $processor3   = static fn (array $record): array => $record;
        $processors   = [
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

        $mandrillHandlerFactory = new MandrillHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $mandrillHandlerFactory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
