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
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\OverflowHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class OverflowHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, thresholdMap?: array{debug?: int, info?: int, notice?: int, warning?: int, error?: int, critical?: int, alert?: int, emergency?: int}, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
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
    ): OverflowHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $childHandler = $this->getHandler($container, $options['handler']);

        if (!$childHandler instanceof HandlerInterface) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $thresholdMap = [
            Level::Alert->value => $options['thresholdMap'][LogLevel::ALERT] ?? 0,
            Level::Critical->value => $options['thresholdMap'][LogLevel::CRITICAL] ?? 0,
            Level::Debug->value => $options['thresholdMap'][LogLevel::DEBUG] ?? 0,
            Level::Emergency->value => $options['thresholdMap'][LogLevel::EMERGENCY] ?? 0,
            Level::Error->value => $options['thresholdMap'][LogLevel::ERROR] ?? 0,
            Level::Info->value => $options['thresholdMap'][LogLevel::INFO] ?? 0,
            Level::Notice->value => $options['thresholdMap'][LogLevel::NOTICE] ?? 0,
            Level::Warning->value => $options['thresholdMap'][LogLevel::WARNING] ?? 0,
        ];

        $level  = LogLevel::DEBUG;
        $bubble = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new OverflowHandler($childHandler, $thresholdMap, $level, $bubble);

        $this->addFormatter($container, $handler, $options);

        return $handler;
    }
}
