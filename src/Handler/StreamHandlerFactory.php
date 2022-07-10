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

use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_resource;
use function is_string;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class StreamHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                         $requestedName
     * @param array<string, (string|int|bool|resource)>|null $options
     * @phpstan-param array{stream?: (bool|int|string|resource), level?: (Level|LevelName|LogLevel::*), bubble?: bool, filePermission?: int, useLocking?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StreamHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('stream', $options)) {
            throw new ServiceNotCreatedException('The required stream is missing');
        }

        $stream         = $this->getStream($container, $options['stream']);
        $level          = LogLevel::DEBUG;
        $bubble         = true;
        $filePermission = 0644;
        $useLocking     = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('filePermission', $options)) {
            $filePermission = $options['filePermission'];
        }

        if (array_key_exists('useLocking', $options)) {
            $useLocking = $options['useLocking'];
        }

        try {
            $handler = new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', StreamHandler::class),
                0,
                $e
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }

    /**
     * @param bool|int|resource|string $stream
     *
     * @return resource|string|null
     *
     * @throws ServiceNotFoundException
     */
    private function getStream(ContainerInterface $container, $stream)
    {
        if (is_resource($stream)) {
            return $stream;
        }

        if (!is_string($stream)) {
            return null;
        }

        if ($container->has($stream)) {
            try {
                return $container->get($stream);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load stream', 0, $e);
            }
        }

        return $stream;
    }
}
