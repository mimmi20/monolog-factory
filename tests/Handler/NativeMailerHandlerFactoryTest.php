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
use Mimmi20\MonologFactory\Handler\NativeMailerHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
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

use function sprintf;

final class NativeMailerHandlerFactoryTest extends TestCase
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $nativeMailerHandlerFactory($container, '');
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $nativeMailerHandlerFactory($container, '', []);
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
        $to = 'test-to';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $nativeMailerHandlerFactory($container, '', ['to' => $to]);
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
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithConfig3(): void
    {
        $to      = 'test-to';
        $subject = 'test-subject';
        $from    = 'test-from';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $nativeMailerHandler = $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from]);

        self::assertInstanceOf(NativeMailerHandler::class, $nativeMailerHandler);

        self::assertSame(Level::Debug, $nativeMailerHandler->getLevel());
        self::assertTrue($nativeMailerHandler->getBubble());
        self::assertNull($nativeMailerHandler->getContentType());
        self::assertSame('utf-8', $nativeMailerHandler->getEncoding());

        $toP = new ReflectionProperty($nativeMailerHandler, 'to');

        self::assertSame([$to], $toP->getValue($nativeMailerHandler));

        $subjectP = new ReflectionProperty($nativeMailerHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($nativeMailerHandler));

        $mcw = new ReflectionProperty($nativeMailerHandler, 'maxColumnWidth');

        self::assertSame(70, $mcw->getValue($nativeMailerHandler));

        $headersP = new ReflectionProperty($nativeMailerHandler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($nativeMailerHandler));

        self::assertInstanceOf(HtmlFormatter::class, $nativeMailerHandler->getFormatter());

        $proc = new ReflectionProperty($nativeMailerHandler, 'processors');

        $processors = $proc->getValue($nativeMailerHandler);

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
    public function testInvokeWithConfig4(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $nativeMailerHandler = $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding]);

        self::assertInstanceOf(NativeMailerHandler::class, $nativeMailerHandler);

        self::assertSame(Level::Alert, $nativeMailerHandler->getLevel());
        self::assertFalse($nativeMailerHandler->getBubble());
        self::assertSame($contentType, $nativeMailerHandler->getContentType());
        self::assertSame($encoding, $nativeMailerHandler->getEncoding());

        $toP = new ReflectionProperty($nativeMailerHandler, 'to');

        self::assertSame([$to], $toP->getValue($nativeMailerHandler));

        $subjectP = new ReflectionProperty($nativeMailerHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($nativeMailerHandler));

        $mcw = new ReflectionProperty($nativeMailerHandler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($nativeMailerHandler));

        $headersP = new ReflectionProperty($nativeMailerHandler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($nativeMailerHandler));

        self::assertInstanceOf(HtmlFormatter::class, $nativeMailerHandler->getFormatter());

        $proc = new ReflectionProperty($nativeMailerHandler, 'processors');

        $processors = $proc->getValue($nativeMailerHandler);

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
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->createStub(LineFormatter::class);

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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $nativeMailerHandler = $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);

        self::assertInstanceOf(NativeMailerHandler::class, $nativeMailerHandler);

        self::assertSame(Level::Alert, $nativeMailerHandler->getLevel());
        self::assertFalse($nativeMailerHandler->getBubble());
        self::assertSame($contentType, $nativeMailerHandler->getContentType());
        self::assertSame($encoding, $nativeMailerHandler->getEncoding());

        $toP = new ReflectionProperty($nativeMailerHandler, 'to');

        self::assertSame([$to], $toP->getValue($nativeMailerHandler));

        $subjectP = new ReflectionProperty($nativeMailerHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($nativeMailerHandler));

        $mcw = new ReflectionProperty($nativeMailerHandler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($nativeMailerHandler));

        $headersP = new ReflectionProperty($nativeMailerHandler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($nativeMailerHandler));

        self::assertSame($formatter, $nativeMailerHandler->getFormatter());

        $proc = new ReflectionProperty($nativeMailerHandler, 'processors');

        $processors = $proc->getValue($nativeMailerHandler);

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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processors     = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processors     = [
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $nativeMailerHandler = $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);

        self::assertInstanceOf(NativeMailerHandler::class, $nativeMailerHandler);

        self::assertSame(Level::Alert, $nativeMailerHandler->getLevel());
        self::assertFalse($nativeMailerHandler->getBubble());
        self::assertSame($contentType, $nativeMailerHandler->getContentType());
        self::assertSame($encoding, $nativeMailerHandler->getEncoding());

        $toP = new ReflectionProperty($nativeMailerHandler, 'to');

        self::assertSame([$to], $toP->getValue($nativeMailerHandler));

        $subjectP = new ReflectionProperty($nativeMailerHandler, 'subject');

        self::assertSame($subject, $subjectP->getValue($nativeMailerHandler));

        $mcw = new ReflectionProperty($nativeMailerHandler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($nativeMailerHandler));

        $headersP = new ReflectionProperty($nativeMailerHandler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($nativeMailerHandler));

        $proc = new ReflectionProperty($nativeMailerHandler, 'processors');

        $processors = $proc->getValue($nativeMailerHandler);

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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
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
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processor3     = static fn (array $record): array => $record;
        $processors     = [
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

        $nativeMailerHandlerFactory = new NativeMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $nativeMailerHandlerFactory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }
}
