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
use Mimmi20\MonologFactory\Handler\IFTTTHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\IFTTTHandler;
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

final class IFTTTHandlerFactoryTest extends TestCase
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $iftttHandlerFactory($container, '');
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No eventName provided');

        $iftttHandlerFactory($container, '', []);
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
        $eventName = 'test-event';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No secretKey provided');

        $iftttHandlerFactory($container, '', ['eventName' => $eventName]);
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
        $eventName = 'test-event';
        $secretKey = 'test-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $iftttHandler = $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey]);

        self::assertInstanceOf(IFTTTHandler::class, $iftttHandler);

        self::assertSame(Level::Debug, $iftttHandler->getLevel());
        self::assertTrue($iftttHandler->getBubble());

        $en = new ReflectionProperty($iftttHandler, 'eventName');

        self::assertSame($eventName, $en->getValue($iftttHandler));

        $sk = new ReflectionProperty($iftttHandler, 'secretKey');

        self::assertSame($secretKey, $sk->getValue($iftttHandler));

        self::assertInstanceOf(LineFormatter::class, $iftttHandler->getFormatter());

        $proc = new ReflectionProperty($iftttHandler, 'processors');

        $processors = $proc->getValue($iftttHandler);

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
        $eventName = 'test-event';
        $secretKey = 'test-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $iftttHandler = $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(IFTTTHandler::class, $iftttHandler);

        self::assertSame(Level::Alert, $iftttHandler->getLevel());
        self::assertFalse($iftttHandler->getBubble());

        $en = new ReflectionProperty($iftttHandler, 'eventName');

        self::assertSame($eventName, $en->getValue($iftttHandler));

        $sk = new ReflectionProperty($iftttHandler, 'secretKey');

        self::assertSame($secretKey, $sk->getValue($iftttHandler));

        self::assertInstanceOf(LineFormatter::class, $iftttHandler->getFormatter());

        $proc = new ReflectionProperty($iftttHandler, 'processors');

        $processors = $proc->getValue($iftttHandler);

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
        $eventName = 'test-event';
        $secretKey = 'test-key';
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $eventName = 'test-event';
        $secretKey = 'test-key';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $eventName = 'test-event';
        $secretKey = 'test-key';
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $iftttHandler = $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(IFTTTHandler::class, $iftttHandler);

        self::assertSame(Level::Alert, $iftttHandler->getLevel());
        self::assertFalse($iftttHandler->getBubble());

        $en = new ReflectionProperty($iftttHandler, 'eventName');

        self::assertSame($eventName, $en->getValue($iftttHandler));

        $sk = new ReflectionProperty($iftttHandler, 'secretKey');

        self::assertSame($secretKey, $sk->getValue($iftttHandler));

        self::assertSame($formatter, $iftttHandler->getFormatter());

        $proc = new ReflectionProperty($iftttHandler, 'processors');

        $processors = $proc->getValue($iftttHandler);

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
        $eventName = 'test-event';
        $secretKey = 'test-key';
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
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
        $eventName  = 'test-event';
        $secretKey  = 'test-key';
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $eventName  = 'test-event';
        $secretKey  = 'test-key';
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $eventName  = 'test-event';
        $secretKey  = 'test-key';
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $iftttHandler = $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(IFTTTHandler::class, $iftttHandler);

        self::assertSame(Level::Alert, $iftttHandler->getLevel());
        self::assertFalse($iftttHandler->getBubble());

        $en = new ReflectionProperty($iftttHandler, 'eventName');

        self::assertSame($eventName, $en->getValue($iftttHandler));

        $sk = new ReflectionProperty($iftttHandler, 'secretKey');

        self::assertSame($secretKey, $sk->getValue($iftttHandler));

        $proc = new ReflectionProperty($iftttHandler, 'processors');

        $processors = $proc->getValue($iftttHandler);

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
        $eventName  = 'test-event';
        $secretKey  = 'test-key';
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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
        $eventName  = 'test-event';
        $secretKey  = 'test-key';
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

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
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

        $eventName = 'test-event';
        $secretKey = 'test-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $iftttHandlerFactory = new IFTTTHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', IFTTTHandler::class));

        $iftttHandlerFactory($container, '', ['eventName' => $eventName, 'secretKey' => $secretKey, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
