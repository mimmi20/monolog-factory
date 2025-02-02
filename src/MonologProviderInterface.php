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

interface MonologProviderInterface
{
    /**
     * Expected to return array to seed such an object.
     *
     * @return array<string, array<string, string>>
     *
     * @throws void
     */
    public function getMonologConfig(): array;
}
