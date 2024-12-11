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

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\RollbarHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Rollbar\Config;
use Rollbar\RollbarLogger;

use function array_key_exists;
use function is_array;
use function sprintf;

final class RollbarHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{
     *     access_token?: string,
     *     enabled?: bool,
     *     transmit?: bool,
     *     log_payload?: bool,
     *     verbose?: (Config::VERBOSE_NONE|LogLevel::*),
     *     environment?: string,
     *     level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*),
     *     bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): RollbarHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('access_token', $options)) {
            throw new ServiceNotCreatedException('No access token provided');
        }

        $token       = $options['access_token'];
        $enabled     = true;
        $transmit    = true;
        $logPayload  = true;
        $verbose     = Config::VERBOSE_NONE;
        $level       = LogLevel::DEBUG;
        $bubble      = true;
        $environment = 'production';

        if (array_key_exists('enabled', $options)) {
            $enabled = $options['enabled'];
        }

        if (array_key_exists('transmit', $options)) {
            $transmit = $options['transmit'];
        }

        if (array_key_exists('log_payload', $options)) {
            $logPayload = $options['log_payload'];
        }

        if (array_key_exists('verbose', $options)) {
            $verbose = $options['verbose'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('environment', $options)) {
            $environment = $options['environment'];
        }

        try {
            $rollbarLogger = new RollbarLogger(
                [
                    'access_token' => $token,
                    'enabled' => $enabled,
                    'environment' => $environment,
                    'log_payload' => $logPayload,
                    'transmit' => $transmit,
                    'verbose' => $verbose,
                ],
            );
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create service %s', RollbarLogger::class),
                0,
                $e,
            );
        }

        $handler = new RollbarHandler($rollbarLogger, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
