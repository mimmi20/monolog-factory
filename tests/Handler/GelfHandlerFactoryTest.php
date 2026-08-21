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
use Gelf\PublisherInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\GelfHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\GelfHandler;
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

use function interface_exists;
use function sprintf;

final class GelfHandlerFactoryTest extends TestCase
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $gelfHandlerFactory($container, '');
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required publisher class');

        $gelfHandlerFactory($container, '', []);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisherName = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required publisher class');

        $gelfHandlerFactory($container, '', ['publisher' => $publisherName]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisherName = 'test-publisher';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willThrowException(new ServiceNotFoundException());

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load publisher class');

        $gelfHandlerFactory($container, '', ['publisher' => $publisherName]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisherName = 'test-publisher';
        $publisher     = $this->createStub(PublisherInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn($publisher);

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisherName]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Debug, $gelfHandler->getLevel());
        self::assertTrue($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfHandler->getFormatter());

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisherName = 'test-publisher';
        $publisher     = $this->createStub(PublisherInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn($publisher);

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisherName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Alert, $gelfHandler->getLevel());
        self::assertFalse($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfHandler->getFormatter());

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisher]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Debug, $gelfHandler->getLevel());
        self::assertTrue($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfHandler->getFormatter());

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Alert, $gelfHandler->getLevel());
        self::assertFalse($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        self::assertInstanceOf(GelfMessageFormatter::class, $gelfHandler->getFormatter());

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
    public function testInvokeWithConfig7(): void
    {
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisherName = 'test-publisher';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn(value: true);

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', GelfHandler::class));

        $gelfHandlerFactory($container, '', ['publisher' => $publisherName]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);
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

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Alert, $gelfHandler->getLevel());
        self::assertFalse($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        self::assertSame($formatter, $gelfHandler->getFormatter());

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher = $this->createStub(PublisherInterface::class);
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher  = $this->createStub(PublisherInterface::class);
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher  = $this->createStub(PublisherInterface::class);
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

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher  = $this->createStub(PublisherInterface::class);
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

        $gelfHandlerFactory = new GelfHandlerFactory();

        $gelfHandler = $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(GelfHandler::class, $gelfHandler);

        self::assertSame(Level::Alert, $gelfHandler->getLevel());
        self::assertFalse($gelfHandler->getBubble());

        $publisherP = new ReflectionProperty($gelfHandler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($gelfHandler));

        $proc = new ReflectionProperty($gelfHandler, 'processors');

        $processors = $proc->getValue($gelfHandler);

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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher  = $this->createStub(PublisherInterface::class);
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

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        if (!interface_exists(PublisherInterface::class)) {
            self::markTestSkipped(
                'Composer package graylog2/gelf-php is required to use Monolog\'s GelfHandler',
            );
        }

        $publisher  = $this->createStub(PublisherInterface::class);
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

        $gelfHandlerFactory = new GelfHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $gelfHandlerFactory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
