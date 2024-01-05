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
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

use const LOG_USER;

final class SyslogUdpHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    private const DEFAULT_PORT = 514;

    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{host?: string, port?: int, facility?: (int|string), level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, ident?: string, rfc?: SyslogUdpHandler::RFC*}|null $options
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
    ): SyslogUdpHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('host', $options)) {
            throw new ServiceNotCreatedException('No host provided');
        }

        $host     = $options['host'];
        $port     = self::DEFAULT_PORT;
        $facility = LOG_USER;
        $level    = LogLevel::DEBUG;
        $bubble   = true;
        $ident    = 'php';
        $rfc      = SyslogUdpHandler::RFC5424;

        if (array_key_exists('port', $options)) {
            $port = $options['port'];
        }

        if (array_key_exists('facility', $options)) {
            $facility = $options['facility'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('ident', $options)) {
            $ident = $options['ident'];
        }

        if (array_key_exists('rfc', $options)) {
            $rfc = $options['rfc'];
        }

        try {
            $handler = new SyslogUdpHandler($host, $port, $facility, $level, $bubble, $ident, $rfc);
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SyslogUdpHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
