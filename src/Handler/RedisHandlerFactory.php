<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory\Handler;

use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;
use Predis\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Redis;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class RedisHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                             $requestedName
     * @param array<string, (string|int|bool|Client|Redis)>|null $options
     * @phpstan-param array{client?: (bool|string|Client|Redis), key?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool, capSize?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RedisHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('client', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        }

        if ($options['client'] instanceof Client || $options['client'] instanceof Redis) {
            $client = $options['client'];
        } elseif (!is_string($options['client'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        } else {
            try {
                $client = $container->get($options['client']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not load client class for %s class', RedisHandler::class),
                    0,
                    $e
                );
            }

            if (!$client instanceof Client && !$client instanceof Redis) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', RedisHandler::class)
                );
            }
        }

        $key     = '';
        $level   = LogLevel::DEBUG;
        $bubble  = true;
        $capSize = 0;

        if (array_key_exists('key', $options)) {
            $key = $options['key'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('capSize', $options)) {
            $capSize = $options['capSize'];
        }

        try {
            $handler = new RedisHandler(
                $client,
                $key,
                $level,
                $bubble,
                $capSize
            );
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load class %s', RedisHandler::class),
                0,
                $e
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
