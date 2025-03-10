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

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\PsrHandler;
use Monolog\Level;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

final class PsrHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|LoggerInterface|string)>|null $options
     * @phpstan-param array{logger?: (bool|string|LoggerInterface), level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
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
    ): PsrHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('logger', $options)) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required logger class',
            );
        }

        if ($options['logger'] instanceof LoggerInterface) {
            $logger = $options['logger'];
        } elseif (!is_string($options['logger'])) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required logger class',
            );
        } else {
            try {
                $logger = $container->get($options['logger']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load logger class', 0, $e);
            }

            if (!$logger instanceof LoggerInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', PsrHandler::class),
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

        $handler = new PsrHandler($logger, $level, $bubble);

        $this->addFormatter($container, $handler, $options);

        return $handler;
    }
}
