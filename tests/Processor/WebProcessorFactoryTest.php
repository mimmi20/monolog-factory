<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022, Thomas Mueller <mimmi20@live.de>
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
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class WebProcessorFactoryTest extends TestCase
{
    /** @var array<string, string>|null */
    private array | null $serverVar = null;

    protected function setUp(): void
    {
        $this->serverVar = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverVar;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithoutConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithEmptyServerdataConfig(): void
    {
        $_SERVER = ['xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => []]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($_SERVER, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithServerdataConfig(): void
    {
        $serverData = ['xyz' => 'abc'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => $serverData]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($serverData, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithServerdataArrayaccess(): void
    {
        $serverData = new ArrayObject(['xyz' => 'abc']);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => $serverData]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($serverData, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /** @throws Exception */
    public function testInvokeWithServerdataInt(): void
    {
        $serverData = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No serverData service found');

        $factory($container, '', ['serverData' => $serverData]);
    }

    /** @throws Exception */
    public function testInvokeWithServerdataString(): void
    {
        $serverData = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(false);
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No serverData service found');

        $factory($container, '', ['serverData' => $serverData]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithServerdataString2(): void
    {
        $serverData       = 'xyz';
        $serverDataObject = new ArrayObject(['xyz' => 'abc']);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($serverDataObject);

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => $serverData]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($serverDataObject, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            [
                'url' => 'REQUEST_URI',
                'ip' => 'REMOTE_ADDR',
                'http_method' => 'REQUEST_METHOD',
                'server' => 'SERVER_NAME',
                'referrer' => 'HTTP_REFERER',
            ],
            $xf->getValue($processor),
        );
    }

    /** @throws Exception */
    public function testInvokeWithServerdataString3(): void
    {
        $serverData = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new WebProcessorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ServerData');

        $factory($container, '', ['serverData' => $serverData]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithServerdataString4(): void
    {
        $serverData       = 'xyz';
        $serverDataObject = new ArrayObject(['xyz' => 'abc']);
        $extraFields      = ['abc' => 'def'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($serverDataObject);

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => $serverData, 'extraFields' => $extraFields]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($serverDataObject, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            $extraFields,
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithServerdataString5(): void
    {
        $serverData       = 'xyz';
        $serverDataObject = new ArrayObject(['xyz']);
        $extraFields      = 'url';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($serverData)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($serverData)
            ->willReturn($serverDataObject);

        $factory = new WebProcessorFactory();

        $processor = $factory($container, '', ['serverData' => $serverData, 'extraFields' => $extraFields]);

        self::assertInstanceOf(WebProcessor::class, $processor);

        $sd = new ReflectionProperty($processor, 'serverData');

        self::assertSame($serverDataObject, $sd->getValue($processor));

        $xf = new ReflectionProperty($processor, 'extraFields');

        self::assertSame(
            ['url' => 'REQUEST_URI'],
            $xf->getValue($processor),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function testGetServerDataService(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WebProcessorFactory();

        self::assertNull($factory->getServerDataService($container, ''));
    }
}
