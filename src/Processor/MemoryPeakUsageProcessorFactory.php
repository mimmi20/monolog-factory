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
use Monolog\Processor\MemoryPeakUsageProcessor;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class MemoryPeakUsageProcessorFactory implements FactoryInterface
{
    /**
     * @param array<bool>|null $options
     * @phpstan-param array{realUsage?: bool, useFormatting?: bool} $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): MemoryPeakUsageProcessor {
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

        return new MemoryPeakUsageProcessor($realUsage, $useFormatting);
    }
}
