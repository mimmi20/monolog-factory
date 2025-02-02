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
use Monolog\Handler\NewRelicHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class NewRelicHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, appName?: string, explodeArrays?: bool, transactionName?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): NewRelicHandler {
        $level           = LogLevel::DEBUG;
        $bubble          = true;
        $appName         = null;
        $explodeArrays   = false;
        $transactionName = null;

        if (is_array($options)) {
            if (array_key_exists('level', $options)) {
                $level = $options['level'];
            }

            if (array_key_exists('bubble', $options)) {
                $bubble = $options['bubble'];
            }

            if (array_key_exists('appName', $options)) {
                $appName = $options['appName'];
            }

            if (array_key_exists('explodeArrays', $options)) {
                $explodeArrays = $options['explodeArrays'];
            }

            if (array_key_exists('transactionName', $options)) {
                $transactionName = $options['transactionName'];
            }
        }

        $handler = new NewRelicHandler($level, $bubble, $appName, $explodeArrays, $transactionName);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
