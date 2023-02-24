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

namespace Mimmi20\MonologFactory\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class IntrospectionProcessorFactory implements FactoryInterface
{
    /**
     * @param string                                              $requestedName
     * @param array<string, (int|string|array<int, string>)>|null $options
     * @phpstan-param array{level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), skipClassesPartials?: array<int, string>|string, skipStackFramesCount?: int}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): IntrospectionProcessor {
        $level          = LogLevel::DEBUG;
        $skipPartials   = [];
        $skipFrameCount = 0;

        if (is_array($options)) {
            if (array_key_exists('level', $options)) {
                $level = $options['level'];
            }

            if (array_key_exists('skipClassesPartials', $options)) {
                $skipPartials = (array) $options['skipClassesPartials'];
            }

            if (array_key_exists('skipStackFramesCount', $options)) {
                $skipFrameCount = $options['skipStackFramesCount'];
            }
        }

        return new IntrospectionProcessor(
            $level,
            $skipPartials,
            $skipFrameCount,
        );
    }
}
