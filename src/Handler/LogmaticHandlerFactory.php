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

namespace Mimmi20\MonologFactory\Handler;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\LogmaticHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

final class LogmaticHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{token?: string, hostname?: string, appname?: string, useSSL?: bool, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, timeout?: float, writeTimeout?: float, persistent?: bool, chunkSize?: int}|null $options
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
    ): LogmaticHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        $hostname          = $options['hostname'] ?? '';
        $appname           = $options['appname'] ?? '';
        $useSSL            = $options['useSSL'] ?? true;
        $level             = $options['level'] ?? LogLevel::DEBUG;
        $bubble            = $options['bubble'] ?? true;
        $timeout           = $options['timeout'] ?? 0.0;
        $writingTimeout    = $options['writingTimeout'] ?? $options['writeTimeout'] ?? 10.0;
        $connectionTimeout = $options['connectionTimeout'] ?? null;
        $persistent        = $options['persistent'] ?? false;
        $chunkSize         = $options['chunkSize'] ?? null;

        try {
            $handler = new LogmaticHandler(
                $options['token'],
                $hostname,
                $appname,
                $useSSL,
                $level,
                $bubble,
                $persistent,
                $timeout,
                $writingTimeout,
                $connectionTimeout,
                $chunkSize,
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', LogmaticHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
