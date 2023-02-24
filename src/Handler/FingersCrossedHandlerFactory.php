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

namespace Mimmi20\MonologFactory\Handler;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Mimmi20\MonologFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

final class FingersCrossedHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                                       $requestedName
     * @param array<string, (int|string|ActivationStrategyInterface)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, activationStrategy?: (null|value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*|ActivationStrategyInterface|array{type?: string, options?: array<mixed>}|string), bufferSize?: int, bubble?: bool, stopBuffering?: bool, passthruLevel?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*)}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerExceptionInterface if any other error occurs
     * @throws InvalidServiceException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): FingersCrossedHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (!$handler instanceof HandlerInterface) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $activationStrategy = null;
        $bufferSize         = 0;
        $bubble             = true;
        $stopBuffering      = true;
        $passthruLevel      = null;

        if (array_key_exists('activationStrategy', $options)) {
            $activationStrategy = $this->getActivationStrategy($container, $options['activationStrategy']);
        }

        if (array_key_exists('bufferSize', $options)) {
            $bufferSize = $options['bufferSize'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('stopBuffering', $options)) {
            $stopBuffering = $options['stopBuffering'];
        }

        if (array_key_exists('passthruLevel', $options)) {
            $passthruLevel = $options['passthruLevel'];
        }

        $handler = new FingersCrossedHandler(
            $handler,
            $activationStrategy,
            $bufferSize,
            $bubble,
            $stopBuffering,
            $passthruLevel,
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }

    /**
     * @param ActivationStrategyInterface|array<string, array<mixed>|string>|string $activationStrategy
     * @phpstan-param (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*|ActivationStrategyInterface|array{type?: string, options?: array<mixed>}|string|null) $activationStrategy
     *
     * @phpstan-return (Level|ActivationStrategyInterface|null)
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws InvalidServiceException
     */
    private function getActivationStrategy(
        ContainerInterface $container,
        ActivationStrategyInterface | Level | int | array | string | null $activationStrategy,
    ): ActivationStrategyInterface | Level | null {
        if (null === $activationStrategy) {
            return null;
        }

        if ($activationStrategy instanceof ActivationStrategyInterface || $activationStrategy instanceof Level) {
            return $activationStrategy;
        }

        try {
            $activationStrategyPluginManager = $container->get(ActivationStrategyPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load service %s', ActivationStrategyPluginManager::class),
                0,
                $e,
            );
        }

        assert(
            $activationStrategyPluginManager instanceof ActivationStrategyPluginManager || $activationStrategyPluginManager instanceof AbstractPluginManager,
            sprintf(
                '$monologHandlerPluginManager should be an Instance of %s, but was %s',
                AbstractPluginManager::class,
                is_object($activationStrategyPluginManager) ? $activationStrategyPluginManager::class : gettype($activationStrategyPluginManager),
            ),
        );

        if (is_array($activationStrategy)) {
            if (!array_key_exists('type', $activationStrategy)) {
                throw new ServiceNotCreatedException('Options must contain a type for the ActivationStrategy');
            }

            try {
                $strategy = $activationStrategyPluginManager->get($activationStrategy['type'], $activationStrategy['options'] ?? []);
            } catch (ServiceNotFoundException | ServiceNotCreatedException $e) {
                throw new ServiceNotFoundException('Could not load ActivationStrategy class', 0, $e);
            }

            assert($strategy instanceof ActivationStrategyInterface);

            return $strategy;
        }

        if (is_string($activationStrategy) && $activationStrategyPluginManager->has($activationStrategy)) {
            try {
                $strategy = $activationStrategyPluginManager->get($activationStrategy);
            } catch (ServiceNotFoundException | ServiceNotCreatedException $e) {
                throw new ServiceNotFoundException('Could not load ActivationStrategy class', 0, $e);
            }

            assert($strategy instanceof ActivationStrategyInterface);

            return $strategy;
        }

        try {
            /* @phpstan-ignore-next-line */
            return Logger::toMonologLevel($activationStrategy);
        } catch (InvalidArgumentException) {
            // do nothing here
        }

        throw new ServiceNotCreatedException('Could not find Class for ActivationStrategy');
    }
}
