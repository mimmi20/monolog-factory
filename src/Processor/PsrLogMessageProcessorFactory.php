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
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class PsrLogMessageProcessorFactory implements FactoryInterface
{
    /**
     * @param string                            $requestedName
     * @param array<string, (string|bool)>|null $options
     * @phpstan-param array{dateFormat?: string, removeUsedContextFields?: bool}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PsrLogMessageProcessor
    {
        $dateFormat              = null;
        $removeUsedContextFields = false;

        if (is_array($options)) {
            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
            }

            if (array_key_exists('removeUsedContextFields', $options)) {
                $removeUsedContextFields = $options['removeUsedContextFields'];
            }
        }

        return new PsrLogMessageProcessor($dateFormat, $removeUsedContextFields);
    }
}
