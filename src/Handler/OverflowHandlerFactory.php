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
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\OverflowHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class OverflowHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, thresholdMap?: array{debug?: int, info?: int, notice?: int, warning?: int, error?: int, critical?: int, alert?: int, emergency?: int}, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OverflowHandler
    {
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

        $thresholdMap = [
            Logger::DEBUG => $options['thresholdMap'][LogLevel::DEBUG] ?? 0,
            Logger::INFO => $options['thresholdMap'][LogLevel::INFO] ?? 0,
            Logger::NOTICE => $options['thresholdMap'][LogLevel::NOTICE] ?? 0,
            Logger::WARNING => $options['thresholdMap'][LogLevel::WARNING] ?? 0,
            Logger::ERROR => $options['thresholdMap'][LogLevel::ERROR] ?? 0,
            Logger::CRITICAL => $options['thresholdMap'][LogLevel::CRITICAL] ?? 0,
            Logger::ALERT => $options['thresholdMap'][LogLevel::ALERT] ?? 0,
            Logger::EMERGENCY => $options['thresholdMap'][LogLevel::EMERGENCY] ?? 0,
        ];

        $level  = LogLevel::DEBUG;
        $bubble = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new OverflowHandler(
            $handler,
            $thresholdMap,
            $level,
            $bubble
        );

        $this->addFormatter($container, $handler, $options);

        return $handler;
    }
}
