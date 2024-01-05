<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory\Handler\FingersCrossed;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class ChannelLevelActivationStrategyFactory implements FactoryInterface
{
    /**
     * @param string                                                  $requestedName
     * @param array<string, array<string,int|string>|int|string>|null $options
     * @phpstan-param array{defaultActionLevel?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), channelToActionLevel?: array<(value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*)>|null}|null $options
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
    ): ChannelLevelActivationStrategy {
        $defaultActionLevel   = LogLevel::DEBUG;
        $channelToActionLevel = [];

        if (is_array($options)) {
            if (array_key_exists('defaultActionLevel', $options)) {
                $defaultActionLevel = $options['defaultActionLevel'];
            }

            if (
                array_key_exists('channelToActionLevel', $options)
                && is_array($options['channelToActionLevel'])
            ) {
                $channelToActionLevel = $options['channelToActionLevel'];
            }
        }

        return new ChannelLevelActivationStrategy($defaultActionLevel, $channelToActionLevel);
    }
}
