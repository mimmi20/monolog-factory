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
use Mimmi20\MonologFactory\Handler\SyslogUdpHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
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

use const LOG_MAIL;
use const LOG_USER;

final class SyslogUdpHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $syslogUdpHandlerFactory($container, '');
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No host provided');

        $syslogUdpHandlerFactory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfig(): void
    {
        $host = 'test-host';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $syslogUdpHandler = $syslogUdpHandlerFactory($container, '', ['host' => $host]);

        self::assertInstanceOf(SyslogUdpHandler::class, $syslogUdpHandler);

        self::assertSame(Level::Debug, $syslogUdpHandler->getLevel());
        self::assertTrue($syslogUdpHandler->getBubble());

        $identP = new ReflectionProperty($syslogUdpHandler, 'ident');

        self::assertSame('php', $identP->getValue($syslogUdpHandler));

        $rfcP = new ReflectionProperty($syslogUdpHandler, 'rfc');

        self::assertSame(SyslogUdpHandler::RFC5424, $rfcP->getValue($syslogUdpHandler));

        $fa = new ReflectionProperty($syslogUdpHandler, 'facility');

        self::assertSame(LOG_USER, $fa->getValue($syslogUdpHandler));

        $socketP = new ReflectionProperty($syslogUdpHandler, 'socket');

        $socket = $socketP->getValue($syslogUdpHandler);

        $ipP = new ReflectionProperty($socket, 'ip');

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');

        self::assertSame(514, $portP->getValue($socket));

        self::assertInstanceOf(LineFormatter::class, $syslogUdpHandler->getFormatter());

        $proc = new ReflectionProperty($syslogUdpHandler, 'processors');

        $processors = $proc->getValue($syslogUdpHandler);

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
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfig2(): void
    {
        $host     = 'test-host';
        $port     = 4711;
        $facility = LOG_MAIL;
        $ident    = 'test-ident';
        $rfc      = SyslogUdpHandler::RFC3164;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $syslogUdpHandler = $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc]);

        self::assertInstanceOf(SyslogUdpHandler::class, $syslogUdpHandler);

        self::assertSame(Level::Alert, $syslogUdpHandler->getLevel());
        self::assertFalse($syslogUdpHandler->getBubble());

        $identP = new ReflectionProperty($syslogUdpHandler, 'ident');

        self::assertSame($ident, $identP->getValue($syslogUdpHandler));

        $rfcP = new ReflectionProperty($syslogUdpHandler, 'rfc');

        self::assertSame($rfc, $rfcP->getValue($syslogUdpHandler));

        $fa = new ReflectionProperty($syslogUdpHandler, 'facility');

        self::assertSame($facility, $fa->getValue($syslogUdpHandler));

        $socketP = new ReflectionProperty($syslogUdpHandler, 'socket');

        $socket = $socketP->getValue($syslogUdpHandler);

        $ipP = new ReflectionProperty($socket, 'ip');

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');

        self::assertSame($port, $portP->getValue($socket));

        self::assertInstanceOf(LineFormatter::class, $syslogUdpHandler->getFormatter());

        $proc = new ReflectionProperty($syslogUdpHandler, 'processors');

        $processors = $proc->getValue($syslogUdpHandler);

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
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $host      = 'test-host';
        $port      = 4711;
        $facility  = LOG_MAIL;
        $ident     = 'test-ident';
        $rfc       = SyslogUdpHandler::RFC3164;
        $formatter = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        $host      = 'test-host';
        $port      = 4711;
        $facility  = LOG_MAIL;
        $ident     = 'test-ident';
        $rfc       = SyslogUdpHandler::RFC3164;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $host      = 'test-host';
        $port      = 4711;
        $facility  = LOG_MAIL;
        $ident     = 'test-ident';
        $rfc       = SyslogUdpHandler::RFC3164;
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

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $syslogUdpHandler = $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'formatter' => $formatter]);

        self::assertInstanceOf(SyslogUdpHandler::class, $syslogUdpHandler);

        self::assertSame(Level::Alert, $syslogUdpHandler->getLevel());
        self::assertFalse($syslogUdpHandler->getBubble());

        $identP = new ReflectionProperty($syslogUdpHandler, 'ident');

        self::assertSame($ident, $identP->getValue($syslogUdpHandler));

        $rfcP = new ReflectionProperty($syslogUdpHandler, 'rfc');

        self::assertSame($rfc, $rfcP->getValue($syslogUdpHandler));

        $fa = new ReflectionProperty($syslogUdpHandler, 'facility');

        self::assertSame($facility, $fa->getValue($syslogUdpHandler));

        $socketP = new ReflectionProperty($syslogUdpHandler, 'socket');

        $socket = $socketP->getValue($syslogUdpHandler);

        $ipP = new ReflectionProperty($socket, 'ip');

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');

        self::assertSame($port, $portP->getValue($socket));

        self::assertSame($formatter, $syslogUdpHandler->getFormatter());

        $proc = new ReflectionProperty($syslogUdpHandler, 'processors');

        $processors = $proc->getValue($syslogUdpHandler);

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
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $host      = 'test-host';
        $port      = 4711;
        $facility  = LOG_MAIL;
        $ident     = 'test-ident';
        $rfc       = SyslogUdpHandler::RFC3164;
        $formatter = $this->createStub(LineFormatter::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn(value: null);

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $host       = 'test-host';
        $port       = 4711;
        $facility   = LOG_MAIL;
        $ident      = 'test-ident';
        $rfc        = SyslogUdpHandler::RFC3164;
        $processors = true;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndProcessors2(): void
    {
        $host       = 'test-host';
        $port       = 4711;
        $facility   = LOG_MAIL;
        $ident      = 'test-ident';
        $rfc        = SyslogUdpHandler::RFC3164;
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

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndProcessors3(): void
    {
        $host       = 'test-host';
        $port       = 4711;
        $facility   = LOG_MAIL;
        $ident      = 'test-ident';
        $rfc        = SyslogUdpHandler::RFC3164;
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

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $syslogUdpHandler = $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'processors' => $processors]);

        self::assertInstanceOf(SyslogUdpHandler::class, $syslogUdpHandler);

        self::assertSame(Level::Alert, $syslogUdpHandler->getLevel());
        self::assertFalse($syslogUdpHandler->getBubble());

        $identP = new ReflectionProperty($syslogUdpHandler, 'ident');

        self::assertSame($ident, $identP->getValue($syslogUdpHandler));

        $rfcP = new ReflectionProperty($syslogUdpHandler, 'rfc');

        self::assertSame($rfc, $rfcP->getValue($syslogUdpHandler));

        $fa = new ReflectionProperty($syslogUdpHandler, 'facility');

        self::assertSame($facility, $fa->getValue($syslogUdpHandler));

        $socketP = new ReflectionProperty($syslogUdpHandler, 'socket');

        $socket = $socketP->getValue($syslogUdpHandler);

        $ipP = new ReflectionProperty($socket, 'ip');

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');

        self::assertSame($port, $portP->getValue($socket));

        $proc = new ReflectionProperty($syslogUdpHandler, 'processors');

        $processors = $proc->getValue($syslogUdpHandler);

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
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndProcessors4(): void
    {
        $host       = 'test-host';
        $port       = 4711;
        $facility   = LOG_MAIL;
        $ident      = 'test-ident';
        $rfc        = SyslogUdpHandler::RFC3164;
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

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    #[RequiresPhpExtension(extension: 'sockets')]
    public function testInvokeWithConfigAndProcessors5(): void
    {
        $host       = 'test-host';
        $port       = 4711;
        $facility   = LOG_MAIL;
        $ident      = 'test-ident';
        $rfc        = SyslogUdpHandler::RFC3164;
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

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'processors' => $processors]);
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
        if (extension_loaded('sockets')) {
            self::markTestSkipped('This test checks the exception if the sockets extension is missing');
        }

        $host     = 'test-host';
        $port     = 4711;
        $facility = LOG_MAIL;
        $ident    = 'test-ident';
        $rfc      = SyslogUdpHandler::RFC3164;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $syslogUdpHandlerFactory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', SyslogUdpHandler::class));

        $syslogUdpHandlerFactory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc]);
    }
}
