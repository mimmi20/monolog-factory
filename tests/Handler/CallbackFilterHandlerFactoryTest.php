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
use Mimmi20\Monolog\Handler\CallbackFilterHandler;
use Mimmi20\MonologFactory\Handler\CallbackFilterHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class CallbackFilterHandlerFactoryTest extends TestCase
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

        $factory = new CallbackFilterHandlerFactory();

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

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /** @throws Exception */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithLoaderError(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotCreatedException());

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithLoaderError2(): void
    {
        $type = 'abc';

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type = 'abc';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([], $filtersP->getValue($handler));

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
    public function testInvokeWithHandlerConfig2(): void
    {
        $type   = 'abc';
        $filter = static function (): void {
        };

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => $filter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([$filter], $filtersP->getValue($handler));

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
    public function testInvokeWithHandlerConfig3(): void
    {
        $type    = 'abc';
        $filter1 = static function (): void {
        };
        $filter2 = static function (): void {
        };

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($handler));

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
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type      = 'abc';
        $filter1   = static function (): void {
        };
        $filter2   = static function (): void {
        };
        $formatter = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'formatter' => $formatter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type      = 'abc';
        $filter1   = static function (): void {
        };
        $filter2   = static function (): void {
        };
        $formatter = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type      = 'abc';
        $filter1   = static function (): void {
        };
        $filter2   = static function (): void {
        };
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'formatter' => $formatter]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type      = 'abc';
        $filter1   = static function (): void {
        };
        $filter2   = static function (): void {
        };
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $monologFormatterPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);

        self::assertInstanceOf(CallbackFilterHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bb = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($bb->getValue($handler));

        $filtersP = new ReflectionProperty($handler, 'filters');

        self::assertSame([$filter1, $filter2], $filtersP->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type       = 'abc';
        $filter1    = static function (): void {
        };
        $filter2    = static function (): void {
        };
        $processors = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2], 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type       = 'abc';
        $filter1    = static function (): void {
        };
        $filter2    = static function (): void {
        };
        $processors = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['processors' => $processors])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new CallbackFilterHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'level' => LogLevel::ALERT, 'bubble' => false, 'filters' => [$filter1, $filter2]]);
    }
}
