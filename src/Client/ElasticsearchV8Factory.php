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

namespace Mimmi20\MonologFactory\Client;

use Elastic\Elasticsearch\Client as V8Client;
use Elastic\Elasticsearch\ClientBuilder;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Override;
use Psr\Container\ContainerInterface;

use function array_filter;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;

final class ElasticsearchV8Factory implements FactoryInterface
{
    /**
     * @param string                                              $requestedName
     * @param array<string, (array<string>|bool|int|string)>|null $options
     * @phpstan-param array{hosts?: bool|array<string>, retries?: int, api-id?: string, api-key?: string, username?: string, password?: string, metadata?: bool|int}|null $options
     *
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): V8Client
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('hosts', $options)) {
            throw new ServiceNotCreatedException('No Hosts provided');
        }

        if (!is_array($options['hosts'])) {
            throw new ServiceNotCreatedException('No Host data provided');
        }

        $metadata = true;

        $builder = ClientBuilder::create();
        $builder->setHosts(
            array_filter(
                $options['hosts'],
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                static fn (string $host): bool => true,
            ),
        );

        if (array_key_exists('retries', $options)) {
            $builder->setRetries($options['retries']);
        }

        if (array_key_exists('api-id', $options) && array_key_exists('api-key', $options)) {
            assert(is_string($options['api-id']));
            assert(is_string($options['api-key']));

            $builder->setApiKey($options['api-key'], $options['api-id']);
        } elseif (array_key_exists('username', $options) && array_key_exists('password', $options)) {
            $builder->setBasicAuthentication($options['username'], $options['password']);
        }

        if (array_key_exists('metadata', $options)) {
            $metadata = (bool) $options['metadata'];
        }

        $builder->setElasticMetaHeader($metadata);

        return $builder->build();
    }
}
