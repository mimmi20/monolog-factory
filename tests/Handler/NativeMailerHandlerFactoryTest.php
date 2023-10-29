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

        $factory = new NativeMailerHandlerFactory();

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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig(): void
    {
        $to = 'test-to';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $factory($container, '', ['to' => $to]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig2(): void
    {
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $factory($container, '', ['to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig3(): void
    {
        $to      = 'test-to';
        $subject = 'test-subject';
        $from    = 'test-from';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertNull($handler->getContentType());
        self::assertSame('utf-8', $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');

        self::assertSame(70, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig4(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($contentType, $handler->getContentType());
        self::assertSame($encoding, $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($contentType, $handler->getContentType());
        self::assertSame($encoding, $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($contentType, $handler->getContentType());
        self::assertSame($encoding, $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');

        self::assertSame($maxColumnWidth, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }
}
