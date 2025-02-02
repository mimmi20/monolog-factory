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

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\WhatFailureGroupHandler;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function is_array;

final class WhatFailureGroupHandlerFactory
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlersTrait;

    /**
     * @param array<string, (array<string>|bool|iterable)>|null $options
     * @phpstan-param array{handlers?: bool|array<string|array{type?: string, enabled?: bool, options?: array<mixed>}>, bubble?: bool}|null $options
     *
     * @throws InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): WhatFailureGroupHandler {
        if (!is_array($options)) {
            return new WhatFailureGroupHandler([], true);
        }

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        try {
            $handlers = $this->getHandlers($container, $options);
        } catch (ServiceNotCreatedException) {
            return new WhatFailureGroupHandler([], $bubble);
        }

        try {
            $handler = new WhatFailureGroupHandler($handlers, $bubble);
        } catch (InvalidArgumentException) {
            return new WhatFailureGroupHandler([], $bubble);
        }

        try {
            $this->addProcessor($container, $handler, $options);
        } catch (Throwable) {
            // do nothing
        }

        return $handler;
    }
}
