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
use Mimmi20\MonologFactory\Handler\PHPConsoleHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Logger;
use PhpConsole\Connector;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

/**
 * @requires PHP < 8.0
 */
final class PHPConsoleHandlerFactoryTest extends TestCase
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

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     */
    public function testInvokeEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required connector class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvokeConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required connector class');

        $factory($container, '', ['connector' => true]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeConfig2(): void
    {
        $connector = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($connector)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load connector class');

        $factory($container, '', ['connector' => $connector]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeConfig3(): void
    {
        $connectorName = 'abc';
        $connector     = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($connectorName)
            ->willReturn($connector);

        $factory = new PHPConsoleHandlerFactory();

        $handler = $factory($container, '', ['connector' => $connectorName]);

        self::assertInstanceOf(PHPConsoleHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame($connector, $handler->getConnector());
        self::assertIsArray($handler->getOptions());
        self::assertTrue($handler->getOptions()['enabled']);

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeConfig4(): void
    {
        $connectorName = 'abc';
        $connector     = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level         = LogLevel::ALERT;
        $bubble        = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($connectorName)
            ->willReturn($connector);

        $factory = new PHPConsoleHandlerFactory();

        $handler = $factory($container, '', ['connector' => $connectorName, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false]]);

        self::assertInstanceOf(PHPConsoleHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connector, $handler->getConnector());
        self::assertIsArray($handler->getOptions());
        self::assertFalse($handler->getOptions()['enabled']);

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeConfig5(): void
    {
        $connectorName = 'abc';
        $connector     = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level         = LogLevel::ALERT;
        $bubble        = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($connectorName)
            ->willReturn($connector);

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', PHPConsoleHandler::class));

        $factory($container, '', ['connector' => $connectorName, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false, 'abc' => 'xyz']]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeConfig6(): void
    {
        $connector = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $handler = $factory($container, '', ['connector' => $connector]);

        self::assertInstanceOf(PHPConsoleHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame($connector, $handler->getConnector());
        self::assertIsArray($handler->getOptions());
        self::assertTrue($handler->getOptions()['enabled']);

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeConfig7(): void
    {
        $connector = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level     = LogLevel::ALERT;
        $bubble    = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $handler = $factory($container, '', ['connector' => $connector, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false]]);

        self::assertInstanceOf(PHPConsoleHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connector, $handler->getConnector());
        self::assertIsArray($handler->getOptions());
        self::assertFalse($handler->getOptions()['enabled']);

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeConfig8(): void
    {
        $connector = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($connector)
            ->willReturn(true);

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', PHPConsoleHandler::class));

        $factory($container, '', ['connector' => $connector]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $connector = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['connector' => $connector, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false], 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $connector = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level     = LogLevel::ALERT;
        $bubble    = false;
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

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['connector' => $connector, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false], 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $connector = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level     = LogLevel::ALERT;
        $bubble    = false;
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

        $factory = new PHPConsoleHandlerFactory();

        $handler = $factory($container, '', ['connector' => $connector, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false], 'formatter' => $formatter]);

        self::assertInstanceOf(PHPConsoleHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connector, $handler->getConnector());
        self::assertIsArray($handler->getOptions());
        self::assertFalse($handler->getOptions()['enabled']);

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $connector  = $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PHPConsoleHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['connector' => $connector, 'level' => $level, 'bubble' => $bubble, 'options' => ['enabled' => false], 'processors' => $processors]);
    }
}
