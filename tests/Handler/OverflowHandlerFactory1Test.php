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
use Mimmi20\MonologFactory\Handler\OverflowHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\OverflowHandler;
use Monolog\Level;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function sprintf;

final class OverflowHandlerFactory1Test extends TestCase
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

        $factory = new OverflowHandlerFactory();

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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 0,
            Level::Critical->value => 0,
            Level::Debug->value => 0,
            Level::Emergency->value => 0,
            Level::Error->value => 0,
            Level::Info->value => 0,
            Level::Notice->value => 0,
            Level::Warning->value => 0,
        ];
        $formatterClass       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

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

        $factory = new OverflowHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(OverflowHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $thm = new ReflectionProperty($handler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithHandlerConfig2(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

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

        $factory = new OverflowHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(OverflowHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $thm = new ReflectionProperty($handler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type      = 'abc';
        $formatter = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type      = 'abc';
        $formatter = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type      = 'abc';
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager): AbstractPluginManager {
                    if ($var === MonologHandlerPluginManager::class) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

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
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new OverflowHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatterClass]);

        self::assertInstanceOf(OverflowHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $thm = new ReflectionProperty($handler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $type      = 'abc';
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager): AbstractPluginManager {
                    if ($var === MonologHandlerPluginManager::class) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $formatterClass       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatterClass);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

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
            ->with($type, ['formatter' => $formatterClass])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, $monologFormatterPluginManager],
                ],
            );

        $factory = new OverflowHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatterClass]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(OverflowHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $thm = new ReflectionProperty($handler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter5(): void
    {
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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
            ->with($type, ['formatter' => $formatterClass])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [MonologHandlerPluginManager::class, $monologHandlerPluginManager],
                    [MonologFormatterPluginManager::class, null],
                ],
            );

        $factory = new OverflowHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatterClass]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type                 = 'abc';
        $thresholdMapExpected = [
            Level::Alert->value => 17,
            Level::Critical->value => 22,
            Level::Debug->value => 9,
            Level::Emergency->value => 8,
            Level::Error->value => 11,
            Level::Info->value => 99,
            Level::Notice->value => 2,
            Level::Warning->value => 42,
        ];
        $processors           = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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

        $factory = new OverflowHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(OverflowHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $thm = new ReflectionProperty($handler, 'thresholdMap');

        self::assertSame($thresholdMapExpected, $thm->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type       = 'abc';
        $processors = true;

        $thresholdMapSet = [
            LogLevel::ALERT => 17,
            LogLevel::CRITICAL => 22,
            LogLevel::DEBUG => 9,
            LogLevel::EMERGENCY => 8,
            LogLevel::ERROR => 11,
            LogLevel::INFO => 99,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 42,
        ];

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

        $factory = new OverflowHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'thresholdMap' => $thresholdMapSet, 'level' => LogLevel::ALERT, 'bubble' => false]);
    }
}
