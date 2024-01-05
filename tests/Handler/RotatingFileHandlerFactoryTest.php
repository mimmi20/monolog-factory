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
use Mimmi20\MonologFactory\Handler\RotatingFileHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function date;
use function sprintf;

final class RotatingFileHandlerFactoryTest extends TestCase
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

        $factory = new RotatingFileHandlerFactory();

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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No filename provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfig(): void
    {
        $filename = '/tmp/test-file';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig2(): void
    {
        $filename       = '/tmp/test-file';
        $filenameFormat = '{filename}_{date}';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'filenameFormat' => $filenameFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '_' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig3(): void
    {
        $filename   = '/tmp/test-file';
        $dateFormat = RotatingFileHandler::FILE_PER_MONTH;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'dateFormat' => $dateFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig5(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $filenameFormat = '{filename}_{date}';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'filenameFormat' => $filenameFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '_' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig6(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
        $formatter      = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

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
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = '0755';
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame((int) $filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

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
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = '0755';
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
        $processors     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');

        self::assertFalse($ul->getValue($handler));

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
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);
    }
}
