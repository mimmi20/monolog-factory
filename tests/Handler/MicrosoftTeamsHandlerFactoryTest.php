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

use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsHandler;
use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsRecord;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\MicrosoftTeamsHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;
use function sprintf;

/** @requires extension curl */
final class MicrosoftTeamsHandlerFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /** @throws Exception */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No url provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig(): void
    {
        $url = 'test-url';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $handler = $factory($container, '', ['url' => $url]);

        self::assertInstanceOf(MicrosoftTeamsHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'webhookDsn');

        self::assertSame($url, $urlP->getValue($handler));

        $formatP = new ReflectionProperty($handler, 'format');

        self::assertSame('%message%', $formatP->getValue($handler));

        $microsoftTeamsRecord = new ReflectionProperty($handler, 'microsoftTeamsRecord');

        $mtr = $microsoftTeamsRecord->getValue($handler);
        assert($mtr instanceof MicrosoftTeamsRecord);

        self::assertSame('Message', $mtr->getTitle());
        self::assertSame('Date', $mtr->getSubject());

        $emojiP = new ReflectionProperty($mtr, 'emoji');

        self::assertNull($emojiP->getValue($mtr));

        $colorP = new ReflectionProperty($mtr, 'color');

        self::assertNull($colorP->getValue($mtr));

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
    public function testInvokeWithConfig2(): void
    {
        $url     = 'test-url';
        $title   = 'test-title';
        $subject = 'test-subject';
        $emoji   = ';)';
        $color   = '#C00';
        $format  = '%message% %extras%';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'title' => $title, 'subject' => $subject, 'emoji' => $emoji, 'color' => $color, 'format' => $format, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MicrosoftTeamsHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'webhookDsn');

        self::assertSame($url, $urlP->getValue($handler));

        $formatP = new ReflectionProperty($handler, 'format');

        self::assertSame($format, $formatP->getValue($handler));

        $microsoftTeamsRecord = new ReflectionProperty($handler, 'microsoftTeamsRecord');

        $mtr = $microsoftTeamsRecord->getValue($handler);
        assert($mtr instanceof MicrosoftTeamsRecord);

        self::assertSame($title, $mtr->getTitle());
        self::assertSame($subject, $mtr->getSubject());

        $emojiP = new ReflectionProperty($mtr, 'emoji');

        self::assertSame($emoji, $emojiP->getValue($mtr));

        $colorP = new ReflectionProperty($mtr, 'color');

        self::assertSame($color, $colorP->getValue($mtr));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $url       = 'test-url';
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $url       = 'test-url';
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

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $url       = 'test-url';
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

        $factory = new MicrosoftTeamsHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(MicrosoftTeamsHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'webhookDsn');

        self::assertSame($url, $urlP->getValue($handler));

        $formatP = new ReflectionProperty($handler, 'format');

        self::assertSame('%message%', $formatP->getValue($handler));

        $microsoftTeamsRecord = new ReflectionProperty($handler, 'microsoftTeamsRecord');

        $mtr = $microsoftTeamsRecord->getValue($handler);
        assert($mtr instanceof MicrosoftTeamsRecord);

        self::assertSame('Message', $mtr->getTitle());
        self::assertSame('Date', $mtr->getSubject());

        $emojiP = new ReflectionProperty($mtr, 'emoji');

        self::assertNull($emojiP->getValue($mtr));

        $colorP = new ReflectionProperty($mtr, 'color');

        self::assertNull($colorP->getValue($mtr));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $url        = 'test-url';
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
