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
use Monolog\Formatter\ElasticsearchFormatter;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class ElasticsearchFormatterFactory implements FactoryInterface
{
    /**
     * @param array<string, bool|int|string>|null $options
     * @phpstan-param array{index?: string, type?: string, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): ElasticsearchFormatter {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('index', $options)) {
            throw new ServiceNotCreatedException('No index provided');
        }

        $index                 = $options['index'];
        $type                  = '';
        $maxNormalizeDepth     = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;

        if (array_key_exists('type', $options)) {
            $type = $options['type'];
        }

        $formatter = new ElasticsearchFormatter($index, $type);

        if (array_key_exists('maxNormalizeDepth', $options)) {
            $maxNormalizeDepth = $options['maxNormalizeDepth'];
        }

        if (array_key_exists('maxNormalizeItemCount', $options)) {
            $maxNormalizeItemCount = $options['maxNormalizeItemCount'];
        }

        if (array_key_exists('prettyPrint', $options)) {
            $prettyPrint = $options['prettyPrint'];
        }

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
