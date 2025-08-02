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

use AMQPExchange;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\AmqpHandler;
use Monolog\Level;
use Override;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

final class AmqpHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                                         $requestedName
     * @param array<string, (AMQPChannel|AMQPExchange|bool|int|string)>|null $options
     * @phpstan-param array{exchange?: (bool|string|AMQPExchange|AMQPChannel), exchangeName?: string, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): AmqpHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('exchange', $options)) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required exchange class',
            );
        }

        if (
            $options['exchange'] instanceof AMQPExchange
            || $options['exchange'] instanceof AMQPChannel
        ) {
            $exchange = $options['exchange'];
        } elseif (!is_string($options['exchange'])) {
            throw new ServiceNotCreatedException(
                'No Service name provided for the required exchange class',
            );
        } else {
            try {
                $exchange = $container->get($options['exchange']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load exchange class', 0, $e);
            }

            if (!$exchange instanceof AMQPExchange && !$exchange instanceof AMQPChannel) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', AmqpHandler::class),
                );
            }
        }

        $exchangeName = null;
        $level        = LogLevel::DEBUG;
        $bubble       = true;

        if ($exchange instanceof AMQPChannel) {
            $exchangeName = 'log';

            if (array_key_exists('exchangeName', $options)) {
                $exchangeName = $options['exchangeName'];
            }
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new AmqpHandler($exchange, $exchangeName, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
