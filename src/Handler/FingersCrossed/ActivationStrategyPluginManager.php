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

namespace Mimmi20\MonologFactory\Handler\FingersCrossed;

use Laminas\ServiceManager\AbstractPluginManager;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;

final class ActivationStrategyPluginManager extends AbstractPluginManager
{
    /**
     * Allow many processors of the same type (v3)
     *
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $sharedByDefault = false;

    /**
     * An object type that the created instance must be instanced of
     *
     * @var string|null
     * @phpstan-var class-string|null
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $instanceOf = ActivationStrategyInterface::class;

    /**
     * A list of factories (either as string name or callable)
     *
     * @var callable[]|string[]
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $factories = [
        ChannelLevelActivationStrategy::class => ChannelLevelActivationStrategyFactory::class,
        ErrorLevelActivationStrategy::class => ErrorLevelActivationStrategyFactory::class,
    ];
}
