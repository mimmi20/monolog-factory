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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Monolog\Logger;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function assert;
use function get_debug_type;
use function is_array;
use function sprintf;

/**
 * Factory for logger instances.
 */
final class LoggerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Factory for laminas-servicemanager v3.
     *
     * @param array<mixed>|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): Logger {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', 'config'), 0, $e);
        }

        $logConfig = [];

        if (
            is_array($config)
            && array_key_exists('log', $config)
            && is_array($config['log'])
            && array_key_exists($requestedName, $config['log'])
            && is_array($config['log'][$requestedName])
        ) {
            $logConfig = $config['log'][$requestedName];
        }

        try {
            $pluginManager = $container->get(MonologPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not find service %s', MonologPluginManager::class),
                0,
                $e,
            );
        }

        assert(
            $pluginManager instanceof AbstractPluginManager,
            sprintf(
                '$pluginManager should be an Instance of %s, but was %s',
                AbstractPluginManager::class,
                get_debug_type($pluginManager),
            ),
        );

        try {
            $monolog = $pluginManager->build(Logger::class, $logConfig);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not find service %s', Logger::class),
                0,
                $e,
            );
        }

        return $monolog;
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @throws void
     */
    #[Override]
    public function canCreate(ContainerInterface $container, string $requestedName): bool
    {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface) {
            return false;
        }

        return is_array($config)
            && array_key_exists('log', $config)
            && is_array($config['log'])
            && array_key_exists($requestedName, $config['log'])
            && is_array($config['log'][$requestedName]);
    }
}
