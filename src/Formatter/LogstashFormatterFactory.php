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
use Monolog\Formatter\LogstashFormatter;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class LogstashFormatterFactory implements FactoryInterface
{
    /**
     * @param array<string, bool|int|string>|null $options
     * @phpstan-param array{applicationName?: string, systemName?: string, extraPrefix?: string, contextPrefix?: string, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
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
    ): LogstashFormatter {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('applicationName', $options)) {
            throw new ServiceNotCreatedException('No applicationName provided');
        }

        $applicationName       = $options['applicationName'];
        $systemName            = null;
        $extraPrefix           = 'extra';
        $contextPrefix         = 'context';
        $maxNormalizeDepth     = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;

        if (array_key_exists('systemName', $options)) {
            $systemName = $options['systemName'];
        }

        if (array_key_exists('extraPrefix', $options)) {
            $extraPrefix = $options['extraPrefix'];
        }

        if (array_key_exists('contextPrefix', $options)) {
            $contextPrefix = $options['contextPrefix'];
        }

        $formatter = new LogstashFormatter($applicationName, $systemName, $extraPrefix, $contextPrefix);

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
