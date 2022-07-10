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
use Mimmi20\MonologFactory\Handler\SwiftMailerHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Swift_Mailer;
use Swift_Message;

use function sprintf;

final class SwiftMailerHandlerFactoryTest extends TestCase
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

        $factory = new SwiftMailerHandlerFactory();

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

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig(): void
    {
        $mailer = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $factory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig2(): void
    {
        $mailer = 'test-mailer';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailer)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load mailer class');

        $factory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig3(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No message service name or callback provided');

        $factory($container, '', ['mailer' => $mailerName]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig4(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message    = 'test-message';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($message)
            ->willReturn(false);
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Message service found');

        $factory($container, '', ['mailer' => $mailerName, 'message' => $message]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig5(): void
    {
        $mailerName  = 'test-mailer';
        $mailer      = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageName = 'test-message';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$mailerName], [$messageName])
            ->willReturnCallback(
                static function (string $with) use ($mailerName, $mailer): Swift_Mailer {
                    if ($with === $mailerName) {
                        return $mailer;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load service %s', $messageName));

        $factory($container, '', ['mailer' => $mailerName, 'message' => $messageName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig6(): void
    {
        $mailerName  = 'test-mailer';
        $mailer      = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageName = 'test-message';
        $message     = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$mailerName], [$messageName])
            ->willReturnOnConsecutiveCalls($mailer, $message);

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailerName, 'message' => $messageName]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig7(): void
    {
        $mailerName  = 'test-mailer';
        $mailer      = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageName = 'test-message';
        $message     = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([$mailerName], [$messageName])
            ->willReturnOnConsecutiveCalls($mailer, $message);

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailerName, 'message' => $messageName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig8(): void
    {
        $mailer  = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'message' => $message]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig9(): void
    {
        $mailer  = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig10(): void
    {
        $mailer  = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message = static function (): void {
        };

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'message' => $message]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig11(): void
    {
        $mailer  = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message = static function (): void {
        };

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfig12(): void
    {
        $mailer = 'test-mailer';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailer)
            ->willReturn(true);

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SwiftMailerHandler::class));

        $factory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $mailer    = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message   = static function (): void {
        };
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $mailer    = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message   = static function (): void {
        };
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

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $mailer    = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message   = static function (): void {
        };
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

        $factory = new SwiftMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(SwiftMailerHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');
        $mailerP->setAccessible(true);

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'messageTemplate');
        $mt->setAccessible(true);

        self::assertSame($message, $mt->getValue($handler));

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
        $mailer     = $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message    = static function (): void {
        };
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SwiftMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['mailer' => $mailer, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
