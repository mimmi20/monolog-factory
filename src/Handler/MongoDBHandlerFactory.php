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

namespace Mimmi20\MonologFactory\Handler;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use MongoDB\Client;
use MongoDB\Driver\Manager;
use Monolog\Handler\MongoDBHandler;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

final class MongoDBHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{client: (string|Client|Manager), database?: string, collection?: string, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): MongoDBHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('client', $options)) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required service class',
            );
        }

        if (!array_key_exists('database', $options)) {
            throw new ServiceNotCreatedException('No database provided');
        }

        if (!array_key_exists('collection', $options)) {
            throw new ServiceNotCreatedException('No collection provided');
        }

        if ($options['client'] instanceof Client || $options['client'] instanceof Manager) {
            $client = $options['client'];
        } elseif (!is_string($options['client'])) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required service class',
            );
        } else {
            try {
                $client = $container->get($options['client']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not load client class for %s class', MongoDBHandler::class),
                    0,
                    $e,
                );
            }

            if (!$client instanceof Client && !$client instanceof Manager) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', MongoDBHandler::class),
                );
            }
        }

        $database   = $options['database'];
        $collection = $options['collection'];
        $level      = LogLevel::DEBUG;
        $bubble     = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new MongoDBHandler($client, $database, $collection, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
