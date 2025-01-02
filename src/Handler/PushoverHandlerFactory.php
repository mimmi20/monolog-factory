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

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\PushoverHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function extension_loaded;
use function is_array;
use function sprintf;

final class PushoverHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                              $requestedName
     * @param array<string, (array<string>|bool|int|string)>|null $options
     * @phpstan-param array{token?: string, users?: array<string>|string, title?: string, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, useSSL?: bool, highPriorityLevel?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), emergencyLevel?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), retry?: int, expire?: int, timeout?: float, writingTimeout?: float, writeTimeout?: float, connectionTimeout?: float, persistent?: bool, chunkSize?: int}|null $options
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
    ): PushoverHandler {
        if (!extension_loaded('sockets')) {
            throw new ServiceNotCreatedException(
                sprintf('The sockets extension is needed to use the %s', PushoverHandler::class),
            );
        }

        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        if (!array_key_exists('users', $options)) {
            throw new ServiceNotCreatedException('No users provided');
        }

        $title             = $options['title'] ?? null;
        $useSSL            = $options['useSSL'] ?? true;
        $highPriorityLevel = $options['highPriorityLevel'] ?? LogLevel::CRITICAL;
        $emergencyLevel    = $options['emergencyLevel'] ?? LogLevel::EMERGENCY;
        $retry             = $options['retry'] ?? 30;
        $expire            = $options['expire'] ?? 25200;
        $level             = $options['level'] ?? LogLevel::DEBUG;
        $bubble            = $options['bubble'] ?? true;
        $timeout           = $options['timeout'] ?? 0.0;
        $writingTimeout    = $options['writingTimeout'] ?? $options['writeTimeout'] ?? 10.0;
        $connectionTimeout = $options['connectionTimeout'] ?? null;
        $persistent        = $options['persistent'] ?? false;
        $chunkSize         = $options['chunkSize'] ?? null;

        try {
            $handler = new PushoverHandler(
                $options['token'],
                $options['users'],
                $title,
                $level,
                $bubble,
                $useSSL,
                $highPriorityLevel,
                $emergencyLevel,
                $retry,
                $expire,
                $persistent,
                $timeout,
                $writingTimeout,
                $connectionTimeout,
                $chunkSize,
            );
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', PushoverHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
