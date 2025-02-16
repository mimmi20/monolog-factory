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
use Monolog\Handler\CouchDBHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class CouchDBHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{host?: string, port?: int, dbname?: string, username?: string, password?: string, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
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
    ): CouchDBHandler {
        $host     = 'localhost';
        $port     = 5984;
        $dbname   = 'logger';
        $userName = null;
        $password = null;
        $level    = LogLevel::DEBUG;
        $bubble   = true;

        if (is_array($options)) {
            if (array_key_exists('host', $options)) {
                $host = $options['host'];
            }

            if (array_key_exists('port', $options)) {
                $port = $options['port'];
            }

            if (array_key_exists('dbname', $options)) {
                $dbname = $options['dbname'];
            }

            if (array_key_exists('username', $options)) {
                $userName = $options['username'];
            }

            if (array_key_exists('password', $options)) {
                $password = $options['password'];
            }

            if (array_key_exists('level', $options)) {
                $level = $options['level'];
            }

            if (array_key_exists('bubble', $options)) {
                $bubble = $options['bubble'];
            }
        }

        $handler = new CouchDBHandler(
            [
                'dbname' => $dbname,
                'host' => $host,
                'password' => $password,
                'port' => $port,
                'username' => $userName,
            ],
            $level,
            $bubble,
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
