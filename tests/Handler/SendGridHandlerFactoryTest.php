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
use Mimmi20\MonologFactory\Handler\SendGridHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SendGridHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function extension_loaded;
use function sprintf;

final class SendGridHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $sendGridHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiUser is missing');

        $sendGridHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig(): void
    {
        $apiUser = 'test-api-user';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiKey is missing');

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig2(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig3(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig4(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig5(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $sendGridHandler = $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);

        self::assertInstanceOf(SendGridHandler::class, $sendGridHandler);

        self::assertSame(Level::Debug, $sendGridHandler->getLevel());
        self::assertTrue($sendGridHandler->getBubble());

        $apiUserP = new ReflectionProperty($sendGridHandler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($sendGridHandler));

        $apiKeyP = new ReflectionProperty($sendGridHandler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($sendGridHandler));

        $fromP = new ReflectionProperty($sendGridHandler, 'from');

        self::assertSame($from, $fromP->getValue($sendGridHandler));

        $toP = new ReflectionProperty($sendGridHandler, 'to');

        self::assertSame((array) $to, $toP->getValue($sendGridHandler));

        $subjectP = new ReflectionProperty($sendGridHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($sendGridHandler));

        self::assertInstanceOf(HtmlFormatter::class, $sendGridHandler->getFormatter());

        $proc = new ReflectionProperty($sendGridHandler, 'processors');

        $processors = $proc->getValue($sendGridHandler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfig6(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';
        $level   = LogLevel::ALERT;
        $bubble  = false;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $sendGridHandler = $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(SendGridHandler::class, $sendGridHandler);

        self::assertSame(Level::Alert, $sendGridHandler->getLevel());
        self::assertFalse($sendGridHandler->getBubble());

        $apiUserP = new ReflectionProperty($sendGridHandler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($sendGridHandler));

        $apiKeyP = new ReflectionProperty($sendGridHandler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($sendGridHandler));

        $fromP = new ReflectionProperty($sendGridHandler, 'from');

        self::assertSame($from, $fromP->getValue($sendGridHandler));

        $toP = new ReflectionProperty($sendGridHandler, 'to');

        self::assertSame((array) $to, $toP->getValue($sendGridHandler));

        $subjectP = new ReflectionProperty($sendGridHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($sendGridHandler));

        self::assertInstanceOf(HtmlFormatter::class, $sendGridHandler->getFormatter());

        $proc = new ReflectionProperty($sendGridHandler, 'processors');

        $processors = $proc->getValue($sendGridHandler);

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
    public function testInvokeWithoutExtension(): void
    {
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', SendGridHandler::class),
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

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

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $sendGridHandler = $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(SendGridHandler::class, $sendGridHandler);

        self::assertSame(Level::Alert, $sendGridHandler->getLevel());
        self::assertFalse($sendGridHandler->getBubble());

        $apiUserP = new ReflectionProperty($sendGridHandler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($sendGridHandler));

        $apiKeyP = new ReflectionProperty($sendGridHandler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($sendGridHandler));

        $fromP = new ReflectionProperty($sendGridHandler, 'from');

        self::assertSame($from, $fromP->getValue($sendGridHandler));

        $toP = new ReflectionProperty($sendGridHandler, 'to');

        self::assertSame((array) $to, $toP->getValue($sendGridHandler));

        $subjectP = new ReflectionProperty($sendGridHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($sendGridHandler));

        self::assertSame($formatter, $sendGridHandler->getFormatter());

        $proc = new ReflectionProperty($sendGridHandler, 'processors');

        $processors = $proc->getValue($sendGridHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
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

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
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

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $sendGridHandler = $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);

        self::assertInstanceOf(SendGridHandler::class, $sendGridHandler);

        self::assertSame(Level::Alert, $sendGridHandler->getLevel());
        self::assertFalse($sendGridHandler->getBubble());

        $apiUserP = new ReflectionProperty($sendGridHandler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($sendGridHandler));

        $apiKeyP = new ReflectionProperty($sendGridHandler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($sendGridHandler));

        $fromP = new ReflectionProperty($sendGridHandler, 'from');

        self::assertSame($from, $fromP->getValue($sendGridHandler));

        $toP = new ReflectionProperty($sendGridHandler, 'to');

        self::assertSame((array) $to, $toP->getValue($sendGridHandler));

        $subjectP = new ReflectionProperty($sendGridHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($sendGridHandler));

        $proc = new ReflectionProperty($sendGridHandler, 'processors');

        $processors = $proc->getValue($sendGridHandler);

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
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
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
            ->willThrowException(new ServiceNotFoundException());

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'curl')]
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
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

        $sendGridHandlerFactory = new SendGridHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $sendGridHandlerFactory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
