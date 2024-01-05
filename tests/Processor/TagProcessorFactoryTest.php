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

namespace Mimmi20Test\MonologFactory\Processor;

use Mimmi20\MonologFactory\Processor\TagProcessorFactory;
use Monolog\Processor\TagProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;

final class TagProcessorFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new TagProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(TagProcessor::class, $processor);

        $tags = new ReflectionProperty($processor, 'tags');

        self::assertSame([], $tags->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new TagProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(TagProcessor::class, $processor);

        $tags = new ReflectionProperty($processor, 'tags');

        self::assertSame([], $tags->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithLevel(): void
    {
        $tags = ['abc', 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TagProcessorFactory();

        $processor = $factory($container, '', ['tags' => $tags]);

        self::assertInstanceOf(TagProcessor::class, $processor);

        $tagsP = new ReflectionProperty($processor, 'tags');

        self::assertSame($tags, $tagsP->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithTagsAsString(): void
    {
        $tags = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new TagProcessorFactory();

        $processor = $factory($container, '', ['tags' => $tags]);

        self::assertInstanceOf(TagProcessor::class, $processor);

        $tagsP = new ReflectionProperty($processor, 'tags');

        self::assertSame((array) $tags, $tagsP->getValue($processor));
    }
}
