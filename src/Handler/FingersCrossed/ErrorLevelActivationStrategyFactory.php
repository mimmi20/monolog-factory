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

namespace Mimmi20\MonologFactory\Handler\FingersCrossed;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

final class ErrorLevelActivationStrategyFactory implements FactoryInterface
{
    /**
     * @param string                         $requestedName
     * @param array<string, int|string>|null $options
     * @phpstan-param array{actionLevel?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*)}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): ErrorLevelActivationStrategy
    {
        $actionLevel = LogLevel::DEBUG;

        if (is_array($options) && array_key_exists('actionLevel', $options)) {
            $actionLevel = $options['actionLevel'];
        }

        return new ErrorLevelActivationStrategy($actionLevel);
    }
}
