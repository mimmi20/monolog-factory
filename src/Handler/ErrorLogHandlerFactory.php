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
use Monolog\Handler\ErrorLogHandler;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class ErrorLogHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{messageType?: (ErrorLogHandler::OPERATING_SYSTEM|ErrorLogHandler::SAPI), level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, expandNewlines?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): ErrorLogHandler
    {
        $messageType    = ErrorLogHandler::OPERATING_SYSTEM;
        $level          = LogLevel::DEBUG;
        $bubble         = true;
        $expandNewlines = false;

        if (is_array($options)) {
            if (array_key_exists('messageType', $options)) {
                $messageType = $options['messageType'];
            }

            if (array_key_exists('level', $options)) {
                $level = $options['level'];
            }

            if (array_key_exists('bubble', $options)) {
                $bubble = $options['bubble'];
            }

            if (array_key_exists('expandNewlines', $options)) {
                $expandNewlines = $options['expandNewlines'];
            }
        }

        $handler = new ErrorLogHandler($messageType, $level, $bubble, $expandNewlines);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
