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
use Monolog\Formatter\MongoDBFormatter;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class MongoDBFormatterFactory implements FactoryInterface
{
    /** @api */
    public const int DEFAULT_NESTING_LEVEL = 3;

    /**
     * @param array<string, (bool|int)>|null $options
     * @phpstan-param array{maxNestingLevel?: int, exceptionTraceAsString?: bool}|null $options
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
    ): MongoDBFormatter {
        $maxNestingLevel        = self::DEFAULT_NESTING_LEVEL;
        $exceptionTraceAsString = true;

        if (is_array($options)) {
            if (array_key_exists('maxNestingLevel', $options)) {
                $maxNestingLevel = $options['maxNestingLevel'];
            }

            if (array_key_exists('exceptionTraceAsString', $options)) {
                $exceptionTraceAsString = $options['exceptionTraceAsString'];
            }
        }

        return new MongoDBFormatter($maxNestingLevel, $exceptionTraceAsString);
    }
}
