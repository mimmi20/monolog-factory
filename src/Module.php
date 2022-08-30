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

namespace Mimmi20\MonologFactory;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ModuleManager\ModuleManagerInterface;

use function assert;

final class Module implements ConfigProviderInterface, InitProviderInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{service_manager: array{factories: array<class-string, class-string>}, monolog_handlers: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog_processors: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog_formatters: array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog: array{abstract_factories: array<int, class-string>, aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}, monolog_service_clients:array{aliases: array<string|class-string, class-string>, factories: array<class-string, class-string>}}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
            'monolog' => $provider->getMonologConfig(),
            'monolog_handlers' => $provider->getMonologHandlerConfig(),
            'monolog_processors' => $provider->getMonologProcessorConfig(),
            'monolog_formatters' => $provider->getMonologFormatterConfig(),
            'monolog_service_clients' => $provider->getMonologClientConfig(),
        ];
    }

    /**
     * Register specifications for all plugin managers with the ServiceListener.
     */
    public function init(ModuleManagerInterface $manager): void
    {
        $event     = $manager->getEvent();
        $container = $event->getParam('ServiceManager');

        $serviceListener = $container->get('ServiceListener');
        assert($serviceListener instanceof ServiceListenerInterface);

        $serviceListener->addServiceManager(
            MonologPluginManager::class,
            'monolog',
            MonologProviderInterface::class,
            'getMonologConfig',
        );

        $serviceListener->addServiceManager(
            MonologHandlerPluginManager::class,
            'monolog_handlers',
            MonologHandlerProviderInterface::class,
            'getMonologHandlerConfig',
        );

        $serviceListener->addServiceManager(
            MonologProcessorPluginManager::class,
            'monolog_processors',
            MonologProcessorProviderInterface::class,
            'getMonologProcessorConfig',
        );

        $serviceListener->addServiceManager(
            MonologFormatterPluginManager::class,
            'monolog_formatters',
            MonologFormatterProviderInterface::class,
            'getMonologFormatterConfig',
        );

        $serviceListener->addServiceManager(
            ClientPluginManager::class,
            'monolog_service_clients',
            ClientProviderInterface::class,
            'getMonologClientConfig',
        );
    }
}
