<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\MonologFactory\Handler;

use AssertionError;
use CMDISP\MonologMicrosoftTeams\TeamsLogHandler;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Handler\TeamsLogHandlerFactory;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function sprintf;

final class TeamsLogHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithoutConfig(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

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
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithEmptyConfig(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No url provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfig(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url = 'test-url';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

        $handler = $factory($container, '', ['url' => $url]);

        self::assertInstanceOf(TeamsLogHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'url');

        self::assertSame($url, $urlP->getValue($handler));

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
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfig2(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url = 'test-url';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(TeamsLogHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'url');

        self::assertSame($url, $urlP->getValue($handler));

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
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url       = 'test-url';
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigAndFormatter(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url       = 'test-url';
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

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigAndFormatter2(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url       = 'test-url';
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
        $monologFormatterPluginManager->expects(self::never())
            ->method('build');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new TeamsLogHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(TeamsLogHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'url');

        self::assertSame($url, $urlP->getValue($handler));

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
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigAndFormatter3(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url       = 'test-url';
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
            ->willReturn(null);

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    #[RequiresPhpExtension('curl')]
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url        = 'test-url';
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors2(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url        = 'test-url';
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

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');
        $monologProcessorPluginManager->expects(self::once())
            ->method('build')
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

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors3(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url        = 'test-url';
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new TeamsLogHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);

        self::assertInstanceOf(TeamsLogHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $urlP = new ReflectionProperty($handler, 'url');

        self::assertSame($url, $urlP->getValue($handler));

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
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url        = 'test-url';
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function testInvokeWithConfigAndProcessors5(): void
    {
        if (!class_exists(TeamsLogHandler::class)) {
            self::markTestSkipped(
                sprintf('class %s is required for this test', TeamsLogHandler::class),
            );
        }

        $url        = 'test-url';
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn(null);

        $factory = new TeamsLogHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false, 'processors' => $processors]);
    }
}
