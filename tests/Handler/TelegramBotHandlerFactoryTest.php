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
use Mimmi20\MonologFactory\Handler\TelegramBotHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function extension_loaded;
use function sprintf;

final class TelegramBotHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $telegramBotHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No apiKey provided');

        $telegramBotHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig(): void
    {
        $apiKey = 'test-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No channel provided');

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig2(): void
    {
        $apiKey  = 'test-key';
        $channel = 'test-channel';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $telegramBotHandler = $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel]);

        self::assertInstanceOf(TelegramBotHandler::class, $telegramBotHandler);

        self::assertSame(Level::Debug, $telegramBotHandler->getLevel());
        self::assertTrue($telegramBotHandler->getBubble());

        $ak = new ReflectionProperty($telegramBotHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($telegramBotHandler));

        $ch = new ReflectionProperty($telegramBotHandler, 'channel');

        self::assertSame($channel, $ch->getValue($telegramBotHandler));

        $pm = new ReflectionProperty($telegramBotHandler, 'parseMode');

        self::assertNull($pm->getValue($telegramBotHandler));

        $dwpp = new ReflectionProperty($telegramBotHandler, 'disableWebPagePreview');

        self::assertNull($dwpp->getValue($telegramBotHandler));

        $dn = new ReflectionProperty($telegramBotHandler, 'disableNotification');

        self::assertNull($dn->getValue($telegramBotHandler));

        self::assertInstanceOf(LineFormatter::class, $telegramBotHandler->getFormatter());

        $proc = new ReflectionProperty($telegramBotHandler, 'processors');

        $processors = $proc->getValue($telegramBotHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig3(): void
    {
        $apiKey    = 'test-key';
        $channel   = 'test-channel';
        $parseMode = 'HTML';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $telegramBotHandler = $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'parseMode' => $parseMode, 'disableWebPagePreview' => true, 'disableNotification' => false]);

        self::assertInstanceOf(TelegramBotHandler::class, $telegramBotHandler);

        self::assertSame(Level::Alert, $telegramBotHandler->getLevel());
        self::assertFalse($telegramBotHandler->getBubble());

        $ak = new ReflectionProperty($telegramBotHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($telegramBotHandler));

        $ch = new ReflectionProperty($telegramBotHandler, 'channel');

        self::assertSame($channel, $ch->getValue($telegramBotHandler));

        $pm = new ReflectionProperty($telegramBotHandler, 'parseMode');

        self::assertSame($parseMode, $pm->getValue($telegramBotHandler));

        $dwpp = new ReflectionProperty($telegramBotHandler, 'disableWebPagePreview');

        self::assertTrue($dwpp->getValue($telegramBotHandler));

        $dn = new ReflectionProperty($telegramBotHandler, 'disableNotification');

        self::assertFalse($dn->getValue($telegramBotHandler));

        self::assertInstanceOf(LineFormatter::class, $telegramBotHandler->getFormatter());

        $proc = new ReflectionProperty($telegramBotHandler, 'processors');

        $processors = $proc->getValue($telegramBotHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $apiKey    = 'test-key';
        $channel   = 'test-channel';
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        $apiKey    = 'test-key';
        $channel   = 'test-channel';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $apiKey    = 'test-key';
        $channel   = 'test-channel';
        $parseMode = 'MarkdownV2';
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

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $telegramBotHandler = $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'parseMode' => $parseMode, 'disableWebPagePreview' => true, 'disableNotification' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(TelegramBotHandler::class, $telegramBotHandler);

        self::assertSame(Level::Alert, $telegramBotHandler->getLevel());
        self::assertFalse($telegramBotHandler->getBubble());

        $ak = new ReflectionProperty($telegramBotHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($telegramBotHandler));

        $ch = new ReflectionProperty($telegramBotHandler, 'channel');

        self::assertSame($channel, $ch->getValue($telegramBotHandler));

        $pm = new ReflectionProperty($telegramBotHandler, 'parseMode');

        self::assertSame($parseMode, $pm->getValue($telegramBotHandler));

        $dwpp = new ReflectionProperty($telegramBotHandler, 'disableWebPagePreview');

        self::assertTrue($dwpp->getValue($telegramBotHandler));

        $dn = new ReflectionProperty($telegramBotHandler, 'disableNotification');

        self::assertFalse($dn->getValue($telegramBotHandler));

        self::assertSame($formatter, $telegramBotHandler->getFormatter());

        $proc = new ReflectionProperty($telegramBotHandler, 'processors');

        $processors = $proc->getValue($telegramBotHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $apiKey    = 'test-key';
        $channel   = 'test-channel';
        $parseMode = 'MarkdownV2';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'parseMode' => $parseMode, 'disableWebPagePreview' => true, 'disableNotification' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $apiKey     = 'test-key';
        $channel    = 'test-channel';
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $apiKey     = 'test-key';
        $channel    = 'test-channel';
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

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $apiKey     = 'test-key';
        $channel    = 'test-channel';
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

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $telegramBotHandler = $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(TelegramBotHandler::class, $telegramBotHandler);

        self::assertSame(Level::Alert, $telegramBotHandler->getLevel());
        self::assertFalse($telegramBotHandler->getBubble());

        $ak = new ReflectionProperty($telegramBotHandler, 'apiKey');

        self::assertSame($apiKey, $ak->getValue($telegramBotHandler));

        $ch = new ReflectionProperty($telegramBotHandler, 'channel');

        self::assertSame($channel, $ch->getValue($telegramBotHandler));

        $dwpp = new ReflectionProperty($telegramBotHandler, 'disableWebPagePreview');

        self::assertNull($dwpp->getValue($telegramBotHandler));

        $dn = new ReflectionProperty($telegramBotHandler, 'disableNotification');

        self::assertNull($dn->getValue($telegramBotHandler));

        $proc = new ReflectionProperty($telegramBotHandler, 'processors');

        $processors = $proc->getValue($telegramBotHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $apiKey     = 'test-key';
        $channel    = 'test-channel';
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

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $apiKey     = 'test-key';
        $channel    = 'test-channel';
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

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutExtension(): void
    {
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        $apiKey  = 'test-key';
        $channel = 'test-channel';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $telegramBotHandlerFactory = new TelegramBotHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', TelegramBotHandler::class));

        $telegramBotHandlerFactory($container, '', ['apiKey' => $apiKey, 'channel' => $channel, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
