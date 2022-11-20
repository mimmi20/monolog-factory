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

use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\SlackHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function extension_loaded;
use function sprintf;

final class SlackHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @requires extension openssl
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

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
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

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfigWithoutChannel(): void
    {
        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No channel provided');

        $factory($container, '', ['token' => $token]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfig(): void
    {
        $token   = 'token';
        $channel = 'channel';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame(0.0, $handler->getTimeout());
        self::assertSame(10.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        self::assertNull($handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

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
     * @requires extension openssl
     */
    public function testInvokeWithConfig2(): void
    {
        $token             = 'token';
        $channel           = 'channel';
        $userName          = 'user';
        $iconEmoji         = 'icon';
        $excludeFields     = ['abc', 'xyz'];
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
        $persistent        = true;
        $chunkSize         = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfig3(): void
    {
        $token             = 'token';
        $channel           = 'channel';
        $userName          = 'user';
        $iconEmoji         = 'icon';
        $excludeFields     = ['abc', 'xyz'];
        $timeout           = 42.0;
        $writeTimeout      = 120.0;
        $connectionTimeout = 51.0;
        $persistent        = true;
        $chunkSize         = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writingTimeout' => $writeTimeout, 'connectionTimeout' => $connectionTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($connectionTimeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $formatter     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
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

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
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

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
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
            ->willReturn(null);

        $factory = new SlackHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was NULL',
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processors    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processors    = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
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
        $monologProcessorPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
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
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['abc', []], ['xyz', ['efg' => 'ijk']])
            ->willReturnOnConsecutiveCalls($processor1, $processor2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($timeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

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

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new SlackHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was NULL',
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithError(): void
    {
        if (extension_loaded('openssl')) {
            self::markTestSkipped('This test checks the exception if the openssl extension is missing');
        }

        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SlackHandler::class));

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);
    }
}
