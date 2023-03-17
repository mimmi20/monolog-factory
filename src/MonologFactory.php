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

namespace Mimmi20\MonologFactory;

use DateTimeZone;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\ErrorHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function array_reverse;
use function assert;
use function get_debug_type;
use function is_array;
use function is_iterable;
use function is_string;
use function sprintf;

/**
 * Factory for monolog instances.
 */
final class MonologFactory implements FactoryInterface
{
    use CreateProcessorTrait;

    /**
     * @param string $requestedName
     * @phpstan-param array{name?: string, timezone?: (bool|string|DateTimeZone), handlers?: string|array{HandlerInterface|array{enabled?: bool, type?: string, options?: array<mixed>}}, processors?: string|array<((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})>}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerExceptionInterface if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): Logger {
        if (!is_array($options) || !array_key_exists('name', $options)) {
            throw new ServiceNotCreatedException('The name for the monolog logger is missing');
        }

        $monolog = new Logger($options['name']);
        $monolog->pushHandler(new NullHandler());

        if (array_key_exists('timezone', $options)) {
            if (is_string($options['timezone'])) {
                try {
                    $timezone = new DateTimeZone($options['timezone']);
                } catch (Throwable $e) {
                    throw new ServiceNotCreatedException('An invalid timezone was set', 0, $e);
                }
            } elseif ($options['timezone'] instanceof DateTimeZone) {
                $timezone = $options['timezone'];
            } else {
                throw new ServiceNotCreatedException('An invalid timezone was set');
            }

            $monolog->setTimezone($timezone);
        }

        if (array_key_exists('handlers', $options)) {
            if (!is_iterable($options['handlers'])) {
                throw new ServiceNotCreatedException('Handlers must be iterable');
            }

            try {
                $monologHandlerPluginManager = $container->get(MonologHandlerPluginManager::class);
                assert(
                    $monologHandlerPluginManager instanceof MonologHandlerPluginManager || $monologHandlerPluginManager instanceof AbstractPluginManager,
                    sprintf(
                        '$monologHandlerPluginManager should be an Instance of %s, but was %s',
                        AbstractPluginManager::class,
                        get_debug_type($monologHandlerPluginManager),
                    ),
                );
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(sprintf('Could not find service %s', MonologHandlerPluginManager::class), 0, $e);
            }

            foreach ($options['handlers'] as $handlerArray) {
                if ($handlerArray instanceof HandlerInterface) {
                    $monolog->pushHandler($handlerArray);

                    continue;
                }

                if (!is_array($handlerArray)) {
                    continue;
                }

                if (array_key_exists('enabled', $handlerArray) && !$handlerArray['enabled']) {
                    continue;
                }

                if (!isset($handlerArray['type'])) {
                    throw new ServiceNotCreatedException('Options must contain a type for the handler');
                }

                try {
                    $handler = $monologHandlerPluginManager->get(
                        $handlerArray['type'],
                        $handlerArray['options'] ?? [],
                    );
                } catch (ContainerExceptionInterface $e) {
                    throw new ServiceNotFoundException(sprintf('Could not find service %s', $handlerArray['type']), 0, $e);
                }

                assert($handler instanceof HandlerInterface);

                $monolog->pushHandler($handler);
            }
        }

        if (array_key_exists('processors', $options)) {
            if (!is_array($options['processors'])) {
                throw new ServiceNotCreatedException('Processors must be an Array');
            }

            try {
                $monologProcessorPluginManager = $container->get(MonologProcessorPluginManager::class);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(sprintf('Could not find service %s', MonologProcessorPluginManager::class), 0, $e);
            }

            foreach (array_reverse($options['processors']) as $processorConfig) {
                $processor = $this->createProcessor($processorConfig, $monologProcessorPluginManager);

                if (null === $processor) {
                    continue;
                }

                $monolog->pushProcessor($processor);
            }
        }

        ErrorHandler::register(
            $monolog,
            $options['errorLevelMap'] ?? false,
            $options['exceptionLevelMap'] ?? false,
            $options['fatalLevel'] ?? false,
        );

        return $monolog;
    }
}
