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

namespace Mimmi20\MonologFactory\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\HostnameProcessor;
use Override;
use Psr\Container\ContainerInterface;

final class HostnameProcessorFactory implements FactoryInterface
{
    /**
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): HostnameProcessor {
        return new HostnameProcessor();
    }
}
