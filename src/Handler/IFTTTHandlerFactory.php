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
use Monolog\Handler\IFTTTHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Level;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

final class IFTTTHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, (bool|int|string)>|null $options
     * @phpstan-param array{eventName?: string, secretKey?: string, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
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
    ): IFTTTHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('eventName', $options)) {
            throw new ServiceNotCreatedException('No eventName provided');
        }

        if (!array_key_exists('secretKey', $options)) {
            throw new ServiceNotCreatedException('No secretKey provided');
        }

        $eventName = $options['eventName'];
        $secretKey = $options['secretKey'];
        $level     = LogLevel::DEBUG;
        $bubble    = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        try {
            $handler = new IFTTTHandler($eventName, $secretKey, $level, $bubble);
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', IFTTTHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
