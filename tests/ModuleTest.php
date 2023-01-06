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

namespace Mimmi20Test\MonologFactory;

use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Mimmi20\MonologFactory\ClientPluginManager;
use Mimmi20\MonologFactory\ClientProviderInterface;
use Mimmi20\MonologFactory\Module;
use Mimmi20\MonologFactory\MonologFormatterPluginManager;
use Mimmi20\MonologFactory\MonologFormatterProviderInterface;
use Mimmi20\MonologFactory\MonologHandlerPluginManager;
use Mimmi20\MonologFactory\MonologHandlerProviderInterface;
use Mimmi20\MonologFactory\MonologPluginManager;
use Mimmi20\MonologFactory\MonologProcessorPluginManager;
use Mimmi20\MonologFactory\MonologProcessorProviderInterface;
use Mimmi20\MonologFactory\MonologProviderInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ModuleTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetConfig(): void
    {
        $module = new Module();

        $config = $module->getConfig();

        self::assertIsArray($config);
        self::assertCount(5, $config);
        self::assertArrayHasKey('service_manager', $config);
        self::assertArrayHasKey('monolog_handlers', $config);
        self::assertArrayHasKey('monolog_processors', $config);
        self::assertArrayHasKey('monolog_formatters', $config);
        self::assertArrayHasKey('monolog_service_clients', $config);
    }

    /** @throws Exception */
    public function testInit(): void
    {
        $serviceListener = $this->getMockBuilder(ServiceListenerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceListener->expects(self::exactly(5))
            ->method('addServiceManager')
            ->withConsecutive(
                [
                    MonologPluginManager::class,
                    'monolog',
                    MonologProviderInterface::class,
                    'getMonologConfig',
                ],
                [
                    MonologHandlerPluginManager::class,
                    'monolog_handlers',
                    MonologHandlerProviderInterface::class,
                    'getMonologHandlerConfig',
                ],
                [
                    MonologProcessorPluginManager::class,
                    'monolog_processors',
                    MonologProcessorProviderInterface::class,
                    'getMonologProcessorConfig',
                ],
                [
                    MonologFormatterPluginManager::class,
                    'monolog_formatters',
                    MonologFormatterProviderInterface::class,
                    'getMonologFormatterConfig',
                ],
                [
                    ClientPluginManager::class,
                    'monolog_service_clients',
                    ClientProviderInterface::class,
                    'getMonologClientConfig',
                ],
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with('ServiceListener')
            ->willReturn($serviceListener);

        $event = $this->getMockBuilder(ModuleEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(self::once())
            ->method('getParam')
            ->with('ServiceManager')
            ->willReturn($container);

        $manager = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getEvent')
            ->willReturn($event);

        $module = new Module();
        $module->init($manager);
    }
}
