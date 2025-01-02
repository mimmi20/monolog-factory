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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\LogRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function array_reverse;
use function assert;
use function get_debug_type;
use function is_array;
use function sprintf;

trait AddProcessorTrait
{
    use CreateProcessorTrait;

    /**
     * @param array<array<string, array<string, mixed>|bool|string>|callable>|null $options
     * @phpstan-param HandlerInterface&ProcessableHandlerInterface $handler
     * @phpstan-param array{processors?: ((callable(LogRecord): LogRecord)|array{enabled?: bool, type?: string, options?: array<mixed>})}|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function addProcessor(
        ContainerInterface $container,
        HandlerInterface $handler,
        array | null $options = null,
    ): void {
        if (!is_array($options) || !array_key_exists('processors', $options)) {
            return;
        }

        if (!is_array($options['processors'])) {
            throw new ServiceNotCreatedException('Processors must be an Array');
        }

        try {
            $monologProcessorPluginManager = $container->get(MonologProcessorPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not find service %s', MonologProcessorPluginManager::class),
                0,
                $e,
            );
        }

        assert(
            $monologProcessorPluginManager instanceof MonologProcessorPluginManager || $monologProcessorPluginManager instanceof AbstractPluginManager,
            sprintf(
                '$monologProcessorPluginManager should be an Instance of %s, but was %s',
                AbstractPluginManager::class,
                get_debug_type($monologProcessorPluginManager),
            ),
        );

        foreach (array_reverse($options['processors']) as $processorConfig) {
            $processor = $this->createProcessor($processorConfig, $monologProcessorPluginManager);

            if ($processor === null) {
                continue;
            }

            $handler->pushProcessor($processor);
        }
    }
}
