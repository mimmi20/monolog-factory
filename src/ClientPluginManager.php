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

use Elastic\Elasticsearch\Client as V8Client;
use Elasticsearch\Client as V7Client;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

use function get_debug_type;
use function sprintf;

/** @extends AbstractPluginManager<V7Client|V8Client> */
final class ClientPluginManager extends AbstractPluginManager
{
    protected string $instanceOf = V8Client::class;

    /**
     * Allow many processors of the same type (v3)
     */
    protected bool $sharedByDefault = false;

    /** @throws InvalidServiceException */
    public function validate(mixed $instance): void
    {
        if ($instance instanceof V8Client || $instance instanceof V7Client) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin manager "%s" expected an instance of type "%s" or of type "%s", but "%s" was received',
            self::class,
            V8Client::class,
            V7Client::class,
            get_debug_type($instance),
        ));
    }
}
