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

namespace Mimmi20\MonologFactory\Handler\FingersCrossed;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Psr\Container\ContainerInterface;

/** @extends AbstractSingleInstancePluginManager<ActivationStrategyInterface> */
final class ActivationStrategyPluginManager extends AbstractSingleInstancePluginManager
{
    /**
     * Allow many processors of the same type (v3)
     */
    protected bool $sharedByDefault = false;

    /**
     * An object type that the created instance must be instanced of
     *
     * @var class-string<ActivationStrategyInterface>
     */
    protected string $instanceOf = ActivationStrategyInterface::class;

    /**
     * A list of factories (either as string name or callable)
     *
     * @phpstan-var array<string, (callable(ContainerInterface, string, array<mixed>|null): mixed)|class-string<callable(ContainerInterface, string, array<mixed>|null): mixed&object>>
     */
    protected array $factories = [
        ChannelLevelActivationStrategy::class => ChannelLevelActivationStrategyFactory::class,
        ErrorLevelActivationStrategy::class => ErrorLevelActivationStrategyFactory::class,
    ];
}
