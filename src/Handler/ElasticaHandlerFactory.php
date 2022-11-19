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

namespace Mimmi20\MonologFactory\Handler;

use Elastica\Client;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\ElasticaHandler;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

final class ElasticaHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                       $requestedName
     * @param array<string, (string|int|bool|Client)>|null $options
     * @phpstan-param array{client?: (bool|string|Client), index?: string, type?: string, ignoreError?: bool, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): ElasticaHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('client', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        }

        if ($options['client'] instanceof Client) {
            $client = $options['client'];
        } elseif (!is_string($options['client'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        } else {
            try {
                $client = $container->get($options['client']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not load client class for %s class', ElasticaHandler::class),
                    0,
                    $e,
                );
            }

            if (!$client instanceof Client) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', ElasticaHandler::class),
                );
            }
        }

        $index       = 'monolog';
        $type        = 'record';
        $ignoreError = false;
        $level       = LogLevel::DEBUG;
        $bubble      = true;

        if (array_key_exists('index', $options)) {
            $index = $options['index'];
        }

        if (array_key_exists('type', $options)) {
            $type = $options['type'];
        }

        if (array_key_exists('ignoreError', $options)) {
            $ignoreError = $options['ignoreError'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new ElasticaHandler(
            $client,
            [
                'index' => $index,
                'type' => $type,
                'ignore_error' => $ignoreError,
            ],
            $level,
            $bubble,
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
