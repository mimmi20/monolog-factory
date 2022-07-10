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

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function assert;
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
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Logger
    {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', 'config'), 0, $e);
        }

        assert(is_array($config));

        $logConfig = [];

        if (
            is_array($config)
            && array_key_exists('monolog', $config)
            && is_array($config['monolog'])
            && array_key_exists($requestedName, $config['monolog'])
            && is_array($config['monolog'][$requestedName])
        ) {
            $logConfig = $config['monolog'][$requestedName];
        }

        try {
            $pluginManager = $container->get(MonologPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotCreatedException(sprintf('Could not find service %s', MonologPluginManager::class), 0, $e);
        }

        try {
            $monolog = $pluginManager->get(Logger::class, $logConfig);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotCreatedException(sprintf('Could not find service %s', Logger::class), 0, $e);
        }

        return $monolog;
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param string $requestedName
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface $e) {
            return false;
        }

        return is_array($config)
            && array_key_exists('monolog', $config)
            && is_array($config['monolog'])
            && array_key_exists($requestedName, $config['monolog']);
    }
}
