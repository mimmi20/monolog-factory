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
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\MonologFactory\Handler\FingersCrossedHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class FingersCrossedHandlerFactoryTest extends TestCase
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

        $factory = new FingersCrossedHandlerFactory();

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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
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

        $factory = new FingersCrossedHandlerFactory();

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
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(0, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertTrue($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertTrue($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertNull($ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => null, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => Logger::WARNING, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithHandlerConfig4(): void
    {
        $type           = 'abc';
        $strategy       = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategy, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithHandlerConfig5(): void
    {
        $type           = 'abc';
        $strategy       = LogLevel::WARNING;
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(false);
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');

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
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithHandlerConfig6(): void
    {
        $type           = 'abc';
        $strategy       = 'xyz';
        $strategyClass  = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
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

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willReturn($strategyClass);

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerConfig7(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnCallback(
                static function (string $with) use ($monologHandlerPluginManager) {
                    if (MonologHandlerPluginManager::class === $with) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load service %s', ActivationStrategyPluginManager::class));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerConfig8(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willThrowException(new ServiceNotFoundException());

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ActivationStrategy class');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithHandlerConfig9(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatterClass  = $this->getMockBuilder(LineFormatter::class)
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

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerConfig10(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willThrowException(new ServiceNotFoundException());

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ActivationStrategy class');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerConfig11(): void
    {
        $type = 'abc';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the ActivationStrategy');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => [], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerConfig12(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(false);
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not find Class for ActivationStrategy');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter       = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $formatter       = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
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
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager, $activationStrategyPluginManager) {
                    if (MonologHandlerPluginManager::class === $var) {
                        return $monologHandlerPluginManager;
                    }

                    if (ActivationStrategyPluginManager::class === $var) {
                        return $activationStrategyPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatter);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager, $monologFormatterPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING, 'formatter' => $formatter]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $formatter       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
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
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager) {
                    if (MonologHandlerPluginManager::class === $var) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter       = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatter);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $monologFormatterPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));

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
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $strategyClass   = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processors      = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategyName, $strategyOptions)
            ->willReturn($strategyClass);

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
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type            = 'abc';
        $strategyName    = 'xyz';
        $strategyOptions = ['level' => 123];
        $processors      = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');

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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'activationStrategy' => ['type' => $strategyName, 'options' => $strategyOptions], 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }
}
