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

namespace Mimmi20Test\MonologFactory\Processor;

use ArrayObject;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\Processor\WebProcessorFactory;
use Monolog\Processor\WebProcessor;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class WebProcessorFactoryTest extends TestCase
{
    /** @var array<string, string>|null */
    private array | null $serverVar = null;

    /** @throws void */
    #[Override]
    protected function setUp(): void
    {
        $this->serverVar = $_SERVER;
    }

    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        $_SERVER = $this->serverVar;
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithoutConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '');

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', []);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithEmptyServerdataConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => []]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataConfig(): void
    {
        $serverData = ['xyz' => 'abc'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => $serverData]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($serverData, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataArrayaccess(): void
    {
        $arrayObject = new ArrayObject(['xyz' => 'abc']);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => $arrayObject]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($arrayObject, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataInt(): void
    {
        $serverData = 42;

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No serverData service found');

        $webProcessorFactory($container, '', ['serverData' => $serverData]);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataString(): void
    {
        $serverData = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(value: false);
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No serverData service found');

        $webProcessorFactory($container, '', ['serverData' => $serverData]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataString2(): void
    {
        $serverData  = 'xyz';
        $arrayObject = new ArrayObject(['xyz' => 'abc']);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($arrayObject);

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => $serverData]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($arrayObject, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertEquals(
            [
                'http_method' => 'REQUEST_METHOD',
                'ip' => 'REMOTE_ADDR',
                'referrer' => 'HTTP_REFERER',
                'server' => 'SERVER_NAME',
                'url' => 'REQUEST_URI',
            ],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataString3(): void
    {
        $serverData = 'xyz';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willThrowException(new ServiceNotFoundException());

        $webProcessorFactory = new WebProcessorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ServerData');

        $webProcessorFactory($container, '', ['serverData' => $serverData]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataString4(): void
    {
        $serverData  = 'xyz';
        $arrayObject = new ArrayObject(['xyz' => 'abc']);
        $extraFields = ['abc' => 'def'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($arrayObject);

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => $serverData, 'extraFields' => $extraFields]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($arrayObject, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertSame(
            $extraFields,
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testInvokeWithServerdataString5(): void
    {
        $serverData  = 'xyz';
        $arrayObject = new ArrayObject(['xyz']);
        $extraFields = 'url';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(value: true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($arrayObject);

        $webProcessorFactory = new WebProcessorFactory();

        $webProcessor = $webProcessorFactory($container, '', ['serverData' => $serverData, 'extraFields' => $extraFields]);

        self::assertInstanceOf(WebProcessor::class, $webProcessor);

        $sd = new ReflectionProperty($webProcessor, 'serverData');

        self::assertSame($arrayObject, $sd->getValue($webProcessor));

        $xf = new ReflectionProperty($webProcessor, 'extraFields');

        self::assertSame(
            ['url' => 'REQUEST_URI'],
            $xf->getValue($webProcessor),
        );
    }

    /**
     * @throws Exception
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws NoPreviousThrowableException
     */
    public function testGetServerDataService(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $webProcessorFactory = new WebProcessorFactory();

        self::assertNull($webProcessorFactory->getServerDataService($container, ''));
    }
}
