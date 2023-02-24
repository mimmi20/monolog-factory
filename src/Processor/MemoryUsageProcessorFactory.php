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

namespace Mimmi20\MonologFactory\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class MemoryUsageProcessorFactory implements FactoryInterface
{
    /**
     * @param string           $requestedName
     * @param array<bool>|null $options
     * @phpstan-param array{realUsage?: bool, useFormatting?: bool} $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): MemoryUsageProcessor {
        $realUsage     = true;
        $useFormatting = true;

        if (is_array($options)) {
            if (array_key_exists('realUsage', $options)) {
                $realUsage = $options['realUsage'];
            }

            if (array_key_exists('useFormatting', $options)) {
                $useFormatting = $options['useFormatting'];
            }
        }

        return new MemoryUsageProcessor($realUsage, $useFormatting);
    }
}
