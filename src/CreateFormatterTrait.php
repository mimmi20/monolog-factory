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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Monolog\Formatter\FormatterInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function sprintf;

trait CreateFormatterTrait
{
    /**
     * @param array<string, array<string, mixed>|bool|string>|FormatterInterface $formatterConfig
     * @phpstan-param FormatterInterface|array{enabled?: bool, type?: string, options?: array<mixed>} $formatterConfig
     * @phpstan-param AbstractPluginManager<FormatterInterface> $monologFormatterPluginManager
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     */
    private function createFormatter(
        array | FormatterInterface $formatterConfig,
        AbstractPluginManager $monologFormatterPluginManager,
    ): FormatterInterface | null {
        if ($formatterConfig instanceof FormatterInterface) {
            return $formatterConfig;
        }

        if (array_key_exists('enabled', $formatterConfig) && !$formatterConfig['enabled']) {
            return null;
        }

        if (!array_key_exists('type', $formatterConfig)) {
            throw new ServiceNotCreatedException('Options must contain a type for the formatter');
        }

        try {
            $formatter = $monologFormatterPluginManager->get(
                $formatterConfig['type'],
                $formatterConfig['options'] ?? [],
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not find service %s', $formatterConfig['type']),
                0,
                $e,
            );
        }

        assert($formatter === null || $formatter instanceof FormatterInterface);

        return $formatter;
    }
}
