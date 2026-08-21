<?php

/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory;

use DateTimeZone;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\ErrorHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Override;
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
     * @phpstan-param array{name?: string, timezone?: (bool|string|DateTimeZone), handlers?: string|array{HandlerInterface|array{enabled?: bool, type?: string, options?: array<mixed>}}, processors?: string|array<((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})>}|null $options
     *
     * @throws ServiceNotCreatedException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): Logger {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('name', $options)) {
            throw new ServiceNotCreatedException('The name for the monolog logger is missing');
        }

        $logger = new Logger($options['name']);
        $logger->pushHandler(new NullHandler());

        if (array_key_exists('timezone', $options)) {
            $this->addTimezone($options, $logger);
        }

        if (array_key_exists('handlers', $options) && is_iterable($options['handlers'])) {
            $this->addHandlers($container, $options, $logger);
        }

        if (array_key_exists('processors', $options) && is_array($options['processors'])) {
            $this->addProcessors($container, $options, $logger);
        }

        ErrorHandler::register(
            $logger,
            $options['errorLevelMap'] ?? false,
            $options['exceptionLevelMap'] ?? false,
            $options['fatalLevel'] ?? false,
        );

        return $logger;
    }

    /**
     * @param array<string, bool>|array<string, string>|array<DateTimeZone>|array<string, DateTimeZone>|callable(Monolog\LogRecord): array<array<Monolog\LogRecord|array<string, array<bool|string|mixed>>|HandlerInterface|array<mixed>>>|array<array<mixed, array<string, array<mixed>>>>|array<array<mixed, array<string, string>>>|array<array<mixed, array<string, bool>>>|array<string, array<mixed>> $options
     * @phpstan-param array{name?: string, timezone: (bool|string|DateTimeZone), handlers?: string|array{HandlerInterface|array{enabled?: bool, type?: string, options?: array<mixed>}}, processors?: string|array<((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})>} $options
     *
     * @throws void
     */
    private function addTimezone(array $options, Logger $logger): void
    {
        if (is_string($options['timezone'])) {
            try {
                $logger->setTimezone(new DateTimeZone($options['timezone']));
            } catch (Throwable) {
                // do nothing
            }

            return;
        }

        if ($options['timezone'] instanceof DateTimeZone) {
            $logger->setTimezone($options['timezone']);
        }
    }

    /**
     * @param array<string, string>|array<string, array<HandlerInterface>>|array<string, array<array<mixed>>>|array<string, array<mixed, array<string, array<mixed>>>>|array<string, array<mixed, array<string, string>>>|array<string, array<mixed, array<string, bool>>>|array<string, bool>|array<string, DateTimeZone>|array<array<HandlerInterface>>|array<array<array<mixed>>>|array<array<mixed, array<string, array<mixed>>>>|array<array<mixed, array<string, string>>>|array<array<mixed, array<string, bool>>>|array<string, array<mixed>> $options
     * @phpstan-param array{name?: string, timezone?: (bool|string|DateTimeZone), handlers: array{HandlerInterface|array{enabled?: bool, type?: string, options?: array<mixed>}}, processors?: string|array<((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})>} $options
     *
     * @throws void
     */
    private function addHandlers(ContainerInterface $container, array $options, Logger $logger): void
    {
        try {
            $monologHandlerPluginManager = $container->get(MonologHandlerPluginManager::class);
            assert(
                $monologHandlerPluginManager instanceof AbstractPluginManager,
                sprintf(
                    '$monologHandlerPluginManager should be an Instance of %s, but was %s',
                    AbstractPluginManager::class,
                    get_debug_type($monologHandlerPluginManager),
                ),
            );
        } catch (Throwable) {
            return;
        }

        foreach ($options['handlers'] as $handlerArray) {
            if ($handlerArray instanceof HandlerInterface) {
                $logger->pushHandler($handlerArray);

                continue;
            }

            if (!is_array($handlerArray)) {
                continue;
            }

            if (array_key_exists('enabled', $handlerArray) && !$handlerArray['enabled']) {
                continue;
            }

            if (!isset($handlerArray['type'])) {
                continue;
            }

            try {
                $handler = $monologHandlerPluginManager->build(
                    $handlerArray['type'],
                    $handlerArray['options'] ?? [],
                );
            } catch (Throwable) {
                continue;
            }

            assert($handler instanceof HandlerInterface);

            $logger->pushHandler($handler);
        }
    }

    /**
     * @param array<string, string>|array<string, array<callable(Monolog\LogRecord): Monolog\LogRecord|array<string, bool|string|array<mixed>>>>|array<string, array<mixed>>|array<string, bool>|array<string, DateTimeZone>|callable(Monolog\LogRecord): array<array<Monolog\LogRecord>>|array<array<array<string, array<bool|string|mixed>>>> $options
     * @phpstan-param array{name?: string, timezone?: (bool|string|DateTimeZone), handlers?: string|array{HandlerInterface|array{enabled?: bool, type?: string, options?: array<mixed>}}, processors: array<((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})>} $options
     *
     * @throws void
     */
    private function addProcessors(ContainerInterface $container, array $options, Logger $logger): void
    {
        try {
            $monologProcessorPluginManager = $container->get(MonologProcessorPluginManager::class);
        } catch (ContainerExceptionInterface) {
            return;
        }

        foreach (array_reverse($options['processors']) as $processorConfig) {
            try {
                $processor = $this->createProcessor($processorConfig, $monologProcessorPluginManager);
            } catch (Throwable) {
                continue;
            }

            if ($processor === null) {
                continue;
            }

            $logger->pushProcessor($processor);
        }
    }
}
