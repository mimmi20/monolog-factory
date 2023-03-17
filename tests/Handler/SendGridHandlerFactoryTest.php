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
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function extension_loaded;
use function sprintf;

final class SendGridHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiUser is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig(): void
    {
        $apiUser = 'test-api-user';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiKey is missing');

        $factory($container, '', ['apiUser' => $apiUser]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig2(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig3(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig4(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig5(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Level::Debug, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfig6(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';
        $level   = LogLevel::ALERT;
        $bubble  = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', SendGridHandler::class),
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
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

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologFormatterPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
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

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Level::Alert, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');

        self::assertSame($subject, $subjectP->getValue($handler));

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
     *
     * @requires extension curl
     */
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologProcessorPluginManager::class),
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            '$monologProcessorPluginManager should be an Instance of Laminas\ServiceManager\AbstractPluginManager, but was null',
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
