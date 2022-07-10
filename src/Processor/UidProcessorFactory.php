<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class UidProcessorFactory implements FactoryInterface
{
    private const DEFAULT_LENGTH = 7;

    /**
     * @param string                  $requestedName
     * @param array<string, int>|null $options
     * @phpstan-param array{length?: int}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UidProcessor
    {
        $length = self::DEFAULT_LENGTH;

        if (is_array($options) && array_key_exists('length', $options)) {
            $length = $options['length'];
        }

        return new UidProcessor($length);
    }
}
