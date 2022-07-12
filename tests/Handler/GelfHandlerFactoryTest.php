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

use Gelf\PublisherInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\GelfHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class GelfHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
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

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required publisher class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig(): void
    {
        $publisherName = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required publisher class');

        $factory($container, '', ['publisher' => $publisherName]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig2(): void
    {
        $publisherName = 'test-publisher';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load publisher class');

        $factory($container, '', ['publisher' => $publisherName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig3(): void
    {
        $publisherName = 'test-publisher';
        $publisher     = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn($publisher);

        $factory = new GelfHandlerFactory();

        $handler = $factory($container, '', ['publisher' => $publisherName]);

        self::assertInstanceOf(GelfHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $publisherP = new ReflectionProperty($handler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($handler));

        self::assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig4(): void
    {
        $publisherName = 'test-publisher';
        $publisher     = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn($publisher);

        $factory = new GelfHandlerFactory();

        $handler = $factory($container, '', ['publisher' => $publisherName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(GelfHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $publisherP = new ReflectionProperty($handler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($handler));

        self::assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig5(): void
    {
        $publisher = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfHandlerFactory();

        $handler = $factory($container, '', ['publisher' => $publisher]);

        self::assertInstanceOf(GelfHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $publisherP = new ReflectionProperty($handler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($handler));

        self::assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig6(): void
    {
        $publisher = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfHandlerFactory();

        $handler = $factory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(GelfHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $publisherP = new ReflectionProperty($handler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($handler));

        self::assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig7(): void
    {
        $publisherName = 'test-publisher';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($publisherName)
            ->willReturn(true);

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', GelfHandler::class));

        $factory($container, '', ['publisher' => $publisherName]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $publisher = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $publisher = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $publisher = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $factory = new GelfHandlerFactory();

        $handler = $factory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(GelfHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $publisherP = new ReflectionProperty($handler, 'publisher');

        self::assertSame($publisher, $publisherP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $publisher  = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['publisher' => $publisher, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
