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
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\SlackHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class SlackHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{token?: string, channel?: string, userName?: string, useAttachment?: bool, iconEmoji?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool, useShortAttachment?: bool, includeContextAndExtra?: bool, excludeFields?: array<string>, timeout?: float, writeTimeout?: float, persistent?: bool, chunkSize?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): SlackHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        if (!array_key_exists('channel', $options)) {
            throw new ServiceNotCreatedException('No channel provided');
        }

        $userName           = $options['userName'] ?? null;
        $useAttachment      = $options['useAttachment'] ?? true;
        $iconEmoji          = $options['iconEmoji'] ?? null;
        $useShortAttachment = $options['useShortAttachment'] ?? false;
        $includeContext     = $options['includeContextAndExtra'] ?? false;
        $excludeFields      = $options['excludeFields'] ?? [];
        $level              = $options['level'] ?? LogLevel::DEBUG;
        $bubble             = $options['bubble'] ?? true;
        $timeout            = $options['timeout'] ?? 0.0;
        $writingTimeout     = $options['writingTimeout'] ?? $options['writeTimeout'] ?? 10.0;
        $connectionTimeout  = $options['connectionTimeout'] ?? null;
        $persistent         = $options['persistent'] ?? false;
        $chunkSize          = $options['chunkSize'] ?? null;

        try {
            $handler = new SlackHandler(
                $options['token'],
                $options['channel'],
                $userName,
                $useAttachment,
                $iconEmoji,
                $level,
                $bubble,
                $useShortAttachment,
                $includeContext,
                $excludeFields,
                $persistent,
                $timeout,
                $writingTimeout,
                $connectionTimeout,
                $chunkSize,
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SlackHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
