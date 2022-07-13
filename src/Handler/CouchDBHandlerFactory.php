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

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\CouchDBHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class CouchDBHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{host?: string, port?: int, dbname?: string, username?: string, password?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CouchDBHandler
    {
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
                'host' => $host,
                'port' => $port,
                'dbname' => $dbname,
                'username' => $userName,
                'password' => $password,
            ],
            $level,
            $bubble
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
