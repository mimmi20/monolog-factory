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
use Mimmi20\MonologFactory\Handler\NewRelicHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\NewRelicHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class NewRelicHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
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

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');

        self::assertNull($an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');

        self::assertFalse($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');

        self::assertNull($tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');

        self::assertNull($an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');

        self::assertFalse($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');

        self::assertNull($tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName]);

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame($appName, $an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');

        self::assertTrue($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');

        self::assertSame($transactionName, $tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');

        self::assertSame($appName, $an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');

        self::assertTrue($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');

        self::assertSame($transactionName, $tn->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $processors      = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'processors' => $processors]);
    }
}
