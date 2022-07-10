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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function is_array;
use function is_callable;
use function sprintf;

trait CreateProcessorTrait
{
    /**
     * @param array<string, array<string, mixed>|bool|string>|callable $processorConfig
     * @phpstan-param callable|array{enabled?: bool, type?: string, options?: array<mixed>} $processorConfig
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function createProcessor($processorConfig, AbstractPluginManager $monologProcessorPluginManager): ?callable
    {
        if (is_callable($processorConfig)) {
            return $processorConfig;
        }

        if (!is_array($processorConfig)) {
            throw new ServiceNotCreatedException('ProcessorConfig must be an Array');
        }

        if (array_key_exists('enabled', $processorConfig) && !$processorConfig['enabled']) {
            return null;
        }

        if (!array_key_exists('type', $processorConfig)) {
            throw new ServiceNotCreatedException('Options must contain a type for the processor');
        }

        try {
            $processor = $monologProcessorPluginManager->get(
                $processorConfig['type'],
                $processorConfig['options'] ?? []
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not find service %s', $processorConfig['type']),
                0,
                $e
            );
        }

        assert(is_callable($processor) || null === $processor);

        return $processor;
    }
}
