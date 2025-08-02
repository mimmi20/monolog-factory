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

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\GelfMessageFormatter;
use Override;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function sprintf;

final class GelfMessageFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{systemName?: string, extraPrefix?: string, contextPrefix?: string, maxLength?: int, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws ServiceNotCreatedException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): GelfMessageFormatter {
        $systemName            = null;
        $extraPrefix           = null;
        $contextPrefix         = 'ctxt_';
        $maxLength             = null;
        $maxNormalizeDepth     = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;

        if (is_array($options)) {
            if (array_key_exists('systemName', $options)) {
                $systemName = $options['systemName'];
            }

            if (array_key_exists('extraPrefix', $options)) {
                $extraPrefix = $options['extraPrefix'];
            }

            if (array_key_exists('contextPrefix', $options)) {
                $contextPrefix = $options['contextPrefix'];
            }

            if (array_key_exists('maxLength', $options)) {
                $maxLength = $options['maxLength'];
            }

            if (array_key_exists('maxNormalizeDepth', $options)) {
                $maxNormalizeDepth = $options['maxNormalizeDepth'];
            }

            if (array_key_exists('maxNormalizeItemCount', $options)) {
                $maxNormalizeItemCount = $options['maxNormalizeItemCount'];
            }

            if (array_key_exists('prettyPrint', $options)) {
                $prettyPrint = $options['prettyPrint'];
            }
        }

        try {
            $formatter = new GelfMessageFormatter(
                $systemName,
                $extraPrefix,
                $contextPrefix,
                $maxLength,
            );
        } catch (RuntimeException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', GelfMessageFormatter::class),
                0,
                $e,
            );
        }

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
