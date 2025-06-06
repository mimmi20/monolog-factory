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

namespace Mimmi20\MonologFactory\Handler;

use Gelf\PublisherInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\GelfHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

final class GelfHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|PublisherInterface|string)>|null $options
     * @phpstan-param array{publisher?: (bool|string|PublisherInterface), level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): GelfHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('publisher', $options)) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required publisher class',
            );
        }

        if ($options['publisher'] instanceof PublisherInterface) {
            $publisher = $options['publisher'];
        } elseif (!is_string($options['publisher'])) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required publisher class',
            );
        } else {
            try {
                $publisher = $container->get($options['publisher']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load publisher class', 0, $e);
            }

            if (!$publisher instanceof PublisherInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', GelfHandler::class),
                );
            }
        }

        $level  = LogLevel::DEBUG;
        $bubble = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new GelfHandler($publisher, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
