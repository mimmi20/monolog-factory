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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Monolog\Formatter\FormatterInterface;

/** @extends AbstractSingleInstancePluginManager<FormatterInterface> */
final class MonologFormatterPluginManager extends AbstractSingleInstancePluginManager
{
    /** @var class-string<FormatterInterface> */
    protected string $instanceOf = FormatterInterface::class;

    /**
     * Allow many processors of the same type (v3)
     */
    protected bool $sharedByDefault = false;
}
