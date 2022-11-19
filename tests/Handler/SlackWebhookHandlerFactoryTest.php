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
use Mimmi20\MonologFactory\Handler\SlackWebhookHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function extension_loaded;
use function sprintf;

final class SlackWebhookHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No webhookUrl provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigWithoutChannel(): void
    {
        $webhookUrl = 'http://test.test';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No channel provided');

        $factory($container, '', ['webhookUrl' => $webhookUrl]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig(): void
    {
        $webhookUrl = 'http://test.test';
        $channel    = 'channel';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $handler = $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel]);

        self::assertInstanceOf(SlackWebhookHandler::class, $handler);
        self::assertSame($webhookUrl, $handler->getWebhookUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $slackRecord = $handler->getSlackRecord();

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
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig2(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $handler = $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields]);

        self::assertInstanceOf(SlackWebhookHandler::class, $handler);
        self::assertSame($webhookUrl, $handler->getWebhookUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $slackRecord = $handler->getSlackRecord();

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

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SlackWebhookHandlerFactory();

        $handler = $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'formatter' => $formatter]);

        self::assertInstanceOf(SlackWebhookHandler::class, $handler);
        self::assertSame($webhookUrl, $handler->getWebhookUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $slackRecord = $handler->getSlackRecord();

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

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $webhookUrl    = 'http://test.test';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $processors    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
    }

    /** @throws Exception */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackWebhookHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SlackWebhookHandler::class));

        $factory($container, '', ['webhookUrl' => $webhookUrl, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'processors' => $processors]);
    }
}
