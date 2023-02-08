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

namespace Mimmi20\MonologFactory\Formatter;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function sprintf;

final class JsonFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{batchMode?: JsonFormatter::BATCH_MODE_*, appendNewline?: bool, ignoreEmptyContextAndExtra?: bool, includeStacktraces?: bool, dateFormat?: string, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws ServiceNotCreatedException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): JsonFormatter
    {
        $batchMode                  = JsonFormatter::BATCH_MODE_JSON;
        $appendNewline              = true;
        $ignoreEmptyContextAndExtra = false;
        $maxNormalizeDepth          = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount      = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint                = false;
        $includeStacktraces         = false;
        $dateFormat                 = NormalizerFormatter::SIMPLE_DATE;

        if (is_array($options)) {
            if (array_key_exists('batchMode', $options)) {
                $batchMode = $options['batchMode'];
            }

            if (array_key_exists('appendNewline', $options)) {
                $appendNewline = $options['appendNewline'];
            }

            if (array_key_exists('ignoreEmptyContextAndExtra', $options)) {
                $ignoreEmptyContextAndExtra = $options['ignoreEmptyContextAndExtra'];
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

            if (array_key_exists('includeStacktraces', $options)) {
                $includeStacktraces = $options['includeStacktraces'];
            }

            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
            }
        }

        try {
            $formatter = new JsonFormatter($batchMode, $appendNewline, $ignoreEmptyContextAndExtra, $includeStacktraces);
        } catch (RuntimeException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', JsonFormatter::class),
                0,
                $e,
            );
        }

        $formatter->setDateFormat($dateFormat);
        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
