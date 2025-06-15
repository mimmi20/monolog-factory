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

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\LoadAverageProcessor;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;
use function sprintf;

final class LoadAverageProcessorFactory implements FactoryInterface
{
    /**
     * @param string                  $requestedName
     * @param array<string, int>|null $options
     * @phpstan-param array{load?: LoadAverageProcessor::LOAD_*}|null $options
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
    ): LoadAverageProcessor {
        $avgSystemLoad = LoadAverageProcessor::LOAD_1_MINUTE;

        if (is_array($options) && array_key_exists('load', $options)) {
            $avgSystemLoad = $options['load'];
        }

        try {
            return new LoadAverageProcessor($avgSystemLoad);
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create service %s', LoadAverageProcessor::class),
                0,
                $e,
            );
        }
    }
}
