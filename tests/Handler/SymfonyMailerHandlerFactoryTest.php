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
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function sprintf;

final class SymfonyMailerHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $symfonyMailerHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $symfonyMailerHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig(): void
    {
        $mailer = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required mailer class');

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig2(): void
    {
        $mailer = 'test-mailer';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailer)
            ->willThrowException(new ServiceNotFoundException());

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load mailer class');

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig3(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->createStub(MailerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Email template provided');

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailerName]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig4(): void
    {
        $mailerName = 'test-mailer';
        $mailer     = $this->createStub(MailerInterface::class);
        $message    = 'test-message';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Email template provided');

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailerName, 'email-template' => $message]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig5(): void
    {
        $mailerName    = 'test-mailer';
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailerName)
            ->willReturn($mailer);

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $symfonyMailerHandler = $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailerName, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $symfonyMailerHandler);

        self::assertSame(Level::Alert, $symfonyMailerHandler->getLevel());
        self::assertFalse($symfonyMailerHandler->getBubble());

        $mailerP = new ReflectionProperty($symfonyMailerHandler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($symfonyMailerHandler));

        self::assertInstanceOf(HtmlFormatter::class, $symfonyMailerHandler->getFormatter());

        $proc = new ReflectionProperty($symfonyMailerHandler, 'processors');

        $processors = $proc->getValue($symfonyMailerHandler);

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
    public function testInvokeWithConfig6(): void
    {
        $mailer = 'test-mailer';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($mailer)
            ->willReturn(value: true);

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SymfonyMailerHandler::class));

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $formatter     = true;
        $emailTemplate = $this->createStub(Email::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);
        $formatter     = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);
        $formatter     = $this->createStub(LineFormatter::class);

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

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $symfonyMailerHandler = $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $symfonyMailerHandler);

        self::assertSame(Level::Alert, $symfonyMailerHandler->getLevel());
        self::assertFalse($symfonyMailerHandler->getBubble());

        $mailerP = new ReflectionProperty($symfonyMailerHandler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($symfonyMailerHandler));

        $mt = new ReflectionProperty($symfonyMailerHandler, 'emailTemplate');

        self::assertSame($emailTemplate, $mt->getValue($symfonyMailerHandler));

        self::assertSame($formatter, $symfonyMailerHandler->getFormatter());

        $proc = new ReflectionProperty($symfonyMailerHandler, 'processors');

        $processors = $proc->getValue($symfonyMailerHandler);

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);
        $formatter     = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);
        $processors    = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);

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

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);

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

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $symfonyMailerHandler = $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(SymfonyMailerHandler::class, $symfonyMailerHandler);

        self::assertSame(Level::Alert, $symfonyMailerHandler->getLevel());
        self::assertFalse($symfonyMailerHandler->getBubble());

        $mailerP = new ReflectionProperty($symfonyMailerHandler, 'mailer');

        self::assertSame($mailer, $mailerP->getValue($symfonyMailerHandler));

        $mt = new ReflectionProperty($symfonyMailerHandler, 'emailTemplate');

        self::assertSame($emailTemplate, $mt->getValue($symfonyMailerHandler));

        $proc = new ReflectionProperty($symfonyMailerHandler, 'processors');

        $processors = $proc->getValue($symfonyMailerHandler);

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
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);
        $processor3    = static fn (array $record): array => $record;
        $processors    = [
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

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $mailer        = $this->createStub(MailerInterface::class);
        $emailTemplate = $this->createStub(Email::class);

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

        $symfonyMailerHandlerFactory = new SymfonyMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $symfonyMailerHandlerFactory($container, '', ['mailer' => $mailer, 'email-template' => $emailTemplate, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
