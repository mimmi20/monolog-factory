<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
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
use Mimmi20\MonologFactory\Handler\SymfonyMailerHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function sprintf;

final class SymfonyMailerHandlerFactoryTest extends TestCase
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

        $factory = new SymfonyMailerHandlerFactory();

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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $factory($container, '', []);
    }

    /** @throws Exception */
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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $factory($container, '', ['mailer' => $mailer]);
    }

    /** @throws Exception */
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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load mailer class');

        $factory($container, '', ['mailer' => $mailer]);
    }

    /** @throws Exception */
    public function testInvokeWithConfig3(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->getMockBuilder(MailerInterface::class)
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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Email template provided');

        $factory($container, '', ['mailer' => $mailerName]);
    }

    /** @throws Exception */
    public function testInvokeWithConfig4(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message    = 'test-message';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Email template provided');

        $factory($container, '', ['mailer' => $mailerName, 'email-template' => $message]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfig5(): void
    {
        $mailerName    = 'test-mailer';
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
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

        $factory = new SymfonyMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailerName, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfig6(): void
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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SymfonyMailerHandler::class));

        $factory($container, '', ['mailer' => $mailer]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter     = true;
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SymfonyMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'emailTemplate');

        self::assertSame($emailTemplate, $mt->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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
            ->willReturn(null);

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processors    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processors = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
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
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
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
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new SymfonyMailerHandlerFactory();

        $handler = $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mailerP = new ReflectionProperty($handler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($handler));

        $mt = new ReflectionProperty($handler, 'emailTemplate');

        self::assertSame($emailTemplate, $mt->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(3, $processors);
        self::assertSame($processor2, $processors[0]);
        self::assertSame($processor1, $processors[1]);
        self::assertSame($processor3, $processors[2]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $mailer        = $this->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailTemplate = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor3 = static fn (array $record): array => $record;
        $processors = [
            [
                'enabled' => true,
                'type' => 'xyz',
                'options' => ['efg' => 'ijk'],
            ],
            [
                'enabled' => false,
                'type' => 'def',
            ],
            ['type' => 'abc'],
            $processor3,
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new SymfonyMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
