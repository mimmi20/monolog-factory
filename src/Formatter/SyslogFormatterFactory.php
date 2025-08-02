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

namespace Mimmi20\MonologFactory\Formatter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\SyslogFormatter;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class SyslogFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{ maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool, applicationName?: string}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): SyslogFormatter {
        $maxNormalizeDepth     = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;
        $applicationName       = '-';

        if (is_array($options)) {
            if (array_key_exists('maxNormalizeDepth', $options)) {
                $maxNormalizeDepth = $options['maxNormalizeDepth'];
            }

            if (array_key_exists('maxNormalizeItemCount', $options)) {
                $maxNormalizeItemCount = $options['maxNormalizeItemCount'];
            }

            if (array_key_exists('prettyPrint', $options)) {
                $prettyPrint = $options['prettyPrint'];
            }

            if (array_key_exists('applicationName', $options)) {
                $applicationName = $options['applicationName'];
            }
        }

        $formatter = new SyslogFormatter($applicationName);

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
