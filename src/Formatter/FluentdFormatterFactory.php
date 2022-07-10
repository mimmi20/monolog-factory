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

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\FluentdFormatter;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class FluentdFormatterFactory implements FactoryInterface
{
    /**
     * @param string                   $requestedName
     * @param array<string, bool>|null $options
     * @phpstan-param array{levelTag?: bool}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FluentdFormatter
    {
        $levelTag = false;

        if (is_array($options) && array_key_exists('levelTag', $options)) {
            $levelTag = $options['levelTag'];
        }

        return new FluentdFormatter($levelTag);
    }
}
