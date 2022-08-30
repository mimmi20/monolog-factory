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

namespace Mimmi20\MonologFactory;

use Laminas\ServiceManager\Config;

interface MonologProviderInterface
{
    /**
     * Expected to return \Laminas\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array<string, array<string, string>>|Config
     */
    public function getMonologConfig(): array | Config;
}
