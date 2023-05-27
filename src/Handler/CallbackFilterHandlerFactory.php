<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory\Handler;

use Closure;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\Monolog\Handler\CallbackFilterHandler;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

use function array_key_exists;
use function is_array;

final class CallbackFilterHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                                                                                           $requestedName
     * @param array<string, (array<(array<(int|string), mixed>|bool|Closure|int|string), mixed>|bool|Closure|int|string)>|null $options
     * @phpstan-param array{handler?: (bool|array{type?: string, enabled?: bool, options?: array<(int|string), mixed>}), filters?: array<int|string, (Closure(LogRecord, Level): bool)>|(Closure(LogRecord, Level): bool), level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerExceptionInterface if any other error occurs
     * @throws RuntimeException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): CallbackFilterHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (!$handler instanceof HandlerInterface) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $filters = [];
        $level   = LogLevel::DEBUG;
        $bubble  = true;

        if (array_key_exists('filters', $options)) {
            $filters = (array) $options['filters'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new CallbackFilterHandler($handler, $filters, $level, $bubble);

        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
