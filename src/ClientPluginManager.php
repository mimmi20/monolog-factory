<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\MonologFactory;

use Elastic\Elasticsearch\Client as V8Client;
use Elasticsearch\Client as V7Client;
use Laminas\ServiceManager\AbstractPluginManager;

/** @extends AbstractPluginManager<V7Client|V8Client> */
final class ClientPluginManager extends AbstractPluginManager
{
    /**
     * Allow many processors of the same type (v3)
     *
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $sharedByDefault = false;
}
