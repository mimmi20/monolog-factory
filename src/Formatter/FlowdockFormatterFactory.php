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

namespace Mimmi20\MonologFactory\Formatter;

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\FlowdockFormatter;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class FlowdockFormatterFactory implements FactoryInterface
{
    /**
     * @param string                     $requestedName
     * @param array<string, string>|null $options
     * @phpstan-param array{source?: string, sourceEmail?: string}|null $options
     *
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FlowdockFormatter
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('source', $options)) {
            throw new ServiceNotCreatedException('No source provided');
        }

        if (!array_key_exists('sourceEmail', $options)) {
            throw new ServiceNotCreatedException('No sourceEmail provided');
        }

        $source      = $options['source'];
        $sourceEmail = $options['sourceEmail'];

        return new FlowdockFormatter($source, $sourceEmail);
    }
}
