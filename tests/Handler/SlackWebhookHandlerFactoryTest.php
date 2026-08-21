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
use Mimmi20\MonologFactory\Handler\SlackWebhookHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackWebhookHandler;
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

final class SlackWebhookHandlerFactoryTest extends TestCase
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $slackWebhookHandlerFactory($container, '');
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No webhookUrl provided');

        $slackWebhookHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigWithoutChannel(): void
    {
        $webhookUrl = 'http://test.test';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No channel provided');

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl]);
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
    public function testInvokeWithConfig(): void
    {
        $webhookUrl = 'http://test.test';
        $channel    = 'channel';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $slackWebhookHandler = $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel]);

        self::assertInstanceOf(SlackWebhookHandler::class, $slackWebhookHandler);
        self::assertSame($webhookUrl, $slackWebhookHandler->getWebhookUrl());
        self::assertSame(Level::Debug, $slackWebhookHandler->getLevel());
        self::assertTrue($slackWebhookHandler->getBubble());

        $slackRecord = $slackWebhookHandler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');

        self::assertNull($un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');

        self::assertTrue($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');

        self::assertNull($ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');

        self::assertFalse($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');

        self::assertFalse($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');

        self::assertSame([], $ef->getValue($slackRecord));

        self::assertInstanceOf(LineFormatter::class, $slackWebhookHandler->getFormatter());

        $proc = new ReflectionProperty($slackWebhookHandler, 'processors');

        $processors = $proc->getValue($slackWebhookHandler);

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
    public function testInvokeWithConfig2(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $slackWebhookHandler = $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields]);

        self::assertInstanceOf(SlackWebhookHandler::class, $slackWebhookHandler);
        self::assertSame($webhookUrl, $slackWebhookHandler->getWebhookUrl());
        self::assertSame(Level::Alert, $slackWebhookHandler->getLevel());
        self::assertFalse($slackWebhookHandler->getBubble());

        $slackRecord = $slackWebhookHandler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');

        self::assertSame($userName, $un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');

        self::assertFalse($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');

        self::assertSame($iconEmoji, $ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');

        self::assertTrue($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');

        self::assertTrue($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');

        self::assertSame($excludeFields, $ef->getValue($slackRecord));

        self::assertInstanceOf(LineFormatter::class, $slackWebhookHandler->getFormatter());

        $proc = new ReflectionProperty($slackWebhookHandler, 'processors');

        $processors = $proc->getValue($slackWebhookHandler);

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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = $this->createStub(LineFormatter::class);

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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $slackWebhookHandler = $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);

        self::assertInstanceOf(SlackWebhookHandler::class, $slackWebhookHandler);
        self::assertSame($webhookUrl, $slackWebhookHandler->getWebhookUrl());
        self::assertSame(Level::Alert, $slackWebhookHandler->getLevel());
        self::assertFalse($slackWebhookHandler->getBubble());

        $slackRecord = $slackWebhookHandler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');

        self::assertSame($userName, $un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');

        self::assertFalse($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');

        self::assertSame($iconEmoji, $ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');

        self::assertTrue($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');

        self::assertTrue($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');

        self::assertSame($excludeFields, $ef->getValue($slackRecord));

        self::assertSame($formatter, $slackWebhookHandler->getFormatter());

        $proc = new ReflectionProperty($slackWebhookHandler, 'processors');

        $processors = $proc->getValue($slackWebhookHandler);

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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processors    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processors    = [
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $slackWebhookHandler = $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);

        self::assertInstanceOf(SlackWebhookHandler::class, $slackWebhookHandler);
        self::assertSame($webhookUrl, $slackWebhookHandler->getWebhookUrl());
        self::assertSame(Level::Alert, $slackWebhookHandler->getLevel());
        self::assertFalse($slackWebhookHandler->getBubble());

        $slackRecord = $slackWebhookHandler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');

        self::assertSame($userName, $un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');

        self::assertFalse($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');

        self::assertSame($iconEmoji, $ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');

        self::assertTrue($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');

        self::assertTrue($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');

        self::assertSame($excludeFields, $ef->getValue($slackRecord));

        $proc = new ReflectionProperty($slackWebhookHandler, 'processors');

        $processors = $proc->getValue($slackWebhookHandler);

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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
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
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
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

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
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
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processors    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $slackWebhookHandlerFactory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SlackWebhookHandler::class));

        $slackWebhookHandlerFactory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
    }
}
