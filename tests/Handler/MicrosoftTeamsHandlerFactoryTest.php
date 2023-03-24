<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Handler;

use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsHandler;
use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsRecord;
use AssertionError;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\MicrosoftTeamsHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function assert;
use function extension_loaded;
use function sprintf;

final class MicrosoftTeamsHandlerFactoryTest extends TestCase
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

        $factory = new MicrosoftTeamsHandlerFactory();

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

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No url provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     *
     * @requires extension curl
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

        self::assertSame(Level::Debug, $handler->getLevel());
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
     *
     * @requires extension curl
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

        self::assertSame(Level::Alert, $handler->getLevel());
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

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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
     *
     * @requires extension curl
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

        self::assertSame(Level::Alert, $handler->getLevel());
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

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter3(): void
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
            ->willReturn(null);

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $url        = 'test-url';
        $processors = [
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

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $url        = 'test-url';
        $processor3 = static fn (array $record): array => $record;
        $processors = [
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
            ->willReturnMap(
                [
                    ['abc', [], $processor1],
                    ['xyz', ['efg' => 'ijk'], $processor2],
                ],
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MicrosoftTeamsHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(MicrosoftTeamsHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
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

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $url        = 'test-url';
        $processor3 = static fn (array $record): array => $record;
        $processors = [
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $url        = 'test-url';
        $processor3 = static fn (array $record): array => $record;
        $processors = [
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

        $factory = new MicrosoftTeamsHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithError(): void
    {
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        $url = 'test-url';

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
        $this->expectExceptionMessage(sprintf('The curl extension is needed to use the %s', MicrosoftTeamsHandler::class));

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
