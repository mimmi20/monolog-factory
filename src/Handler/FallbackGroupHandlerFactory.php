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

namespace Mimmi20\MonologFactory\Handler;

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\FallbackGroupHandler;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;
use function sprintf;

final class FallbackGroupHandlerFactory
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlersTrait;

    /**
     * @param string                                            $requestedName
     * @param array<string, (array<string>|bool|iterable)>|null $options
     * @phpstan-param array{handlers?: bool|array<string|array{type?: string, enabled?: bool, options?: array<mixed>}>, bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): FallbackGroupHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        $handlers = $this->getHandlers($container, $options);

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        try {
            $handler = new FallbackGroupHandler($handlers, $bubble);
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', FallbackGroupHandler::class),
                0,
                $e,
            );
        }

        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
