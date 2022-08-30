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

use Aws\Sqs\SqsClient;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\SqsHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_string;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class SqsHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                          $requestedName
     * @param array<string, (string|int|bool|SqsClient)>|null $options
     * @phpstan-param array{sqsClient?: (bool|string|SqsClient), queueUrl?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): SqsHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('sqsClient', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required sqsClient class');
        }

        if ($options['sqsClient'] instanceof SqsClient) {
            $sqsClient = $options['sqsClient'];
        } elseif (!is_string($options['sqsClient'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required sqsClient class');
        } else {
            try {
                $sqsClient = $container->get($options['sqsClient']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load sqsClient class', 0, $e);
            }

            if (!$sqsClient instanceof SqsClient) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', SqsHandler::class),
                );
            }
        }

        $queueUrl = '';
        $level    = LogLevel::DEBUG;
        $bubble   = true;

        if (array_key_exists('queueUrl', $options)) {
            $queueUrl = $options['queueUrl'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new SqsHandler($sqsClient, $queueUrl, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
