<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2024, Thomas Mueller <mimmi20@live.de>
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
use Mimmi20\MonologFactory\Handler\StreamHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function fopen;
use function sprintf;

final class StreamHandlerFactoryTest extends TestCase
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

        $factory = new StreamHandlerFactory();

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

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required stream is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithStream(): void
    {
        $stream = fopen(__FILE__, 'r');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $stream]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertSame($stream, $handler->getStream());
        self::assertNull($handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertTrue($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfigWithInt(): void
    {
        $stream = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', StreamHandler::class));

        $factory($container, '', ['stream' => $stream]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigWithString(): void
    {
        $stream = 'http://test.test';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($stream)
            ->willReturn(false);
        $container->expects(self::never())
            ->method('get');

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $stream]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertTrue($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfigWithString2(): void
    {
        $streamName = 'xyz';
        $stream     = 'http://test.test';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($streamName)
            ->willReturn($stream);

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertTrue($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfigWithString3(): void
    {
        $streamName = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($streamName)
            ->willThrowException(new ServiceNotCreatedException());

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load stream');

        $factory($container, '', ['stream' => $streamName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($streamName)
            ->willReturn($stream);

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $formatter      = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($streamName)
            ->willReturn($stream);

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $formatter      = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($streamName, $stream): string {
                    if ($var === $streamName) {
                        return $stream;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $formatter      = $this->getMockBuilder(LineFormatter::class)
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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $formatter      = ['enabled' => false];

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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $formatter      = ['enabled' => true];

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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the formatter');

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter5(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $type           = 'elastica';
        $formatter      = ['enabled' => true, 'type' => $type];
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($formatterClass);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter6(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $type           = 'elastica';
        $options        = ['abc' => 'def'];
        $formatter      = ['type' => $type, 'options' => $options];
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with($type, $options)
            ->willReturn($formatterClass);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter7(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $type           = 'elastica';
        $options        = ['abc' => 'def'];
        $formatter      = ['type' => $type, 'options' => $options];

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with($type, $options)
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', $type));

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter8(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $type           = 'elastica';
        $options        = ['abc' => 'def'];
        $formatter      = ['type' => $type, 'options' => $options];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $processors     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($streamName)
            ->willReturn($stream);

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $processors     = [
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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologProcessorPluginManager::class, $monologProcessorPluginManager],
                ],
            );

        $factory = new StreamHandlerFactory();

        $handler = $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'processors' => $processors]);

        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertNull($handler->getStream());
        self::assertSame($stream, $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($streamName, $stream) {
                    if ($var === $streamName) {
                        return $stream;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $streamName     = 'xyz';
        $stream         = 'http://test.test';
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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
        $container->expects(self::once())
            ->method('has')
            ->with($streamName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [$streamName, $stream],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $factory = new StreamHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['stream' => $streamName, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'processors' => $processors]);
    }
}
