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

namespace Mimmi20\MonologFactory\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\TagProcessor;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class TagProcessorFactory implements FactoryInterface
{
    /**
     * @param string                                   $requestedName
     * @param array<string, array<string>|string>|null $options
     * @phpstan-param array{tags?: array<string>|string}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): TagProcessor {
        $tags = [];

        if (is_array($options) && array_key_exists('tags', $options)) {
            $tags = (array) $options['tags'];
        }

        return new TagProcessor($tags);
    }
}
