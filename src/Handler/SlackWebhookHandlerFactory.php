<?php
/**
 * This file is part of the mimmi20/monolog-factory package.
 *
 * Copyright (c) 2022-2023, Thomas Mueller <mimmi20@live.de>
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
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

final class SlackWebhookHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{webhookUrl?: string, channel?: string, userName?: string, useAttachment?: bool, iconEmoji?: string, useShortAttachment?: bool, includeContextAndExtra?: bool, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, excludeFields?: array<string>}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerExceptionInterface if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): SlackWebhookHandler {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('webhookUrl', $options)) {
            throw new ServiceNotCreatedException('No webhookUrl provided');
        }

        if (!array_key_exists('channel', $options)) {
            throw new ServiceNotCreatedException('No channel provided');
        }

        $webhookUrl         = $options['webhookUrl'];
        $channel            = $options['channel'];
        $userName           = null;
        $useAttachment      = true;
        $iconEmoji          = null;
        $useShortAttachment = false;
        $includeContext     = false;
        $level              = LogLevel::DEBUG;
        $bubble             = true;
        $excludeFields      = [];

        if (array_key_exists('userName', $options)) {
            $userName = $options['userName'];
        }

        if (array_key_exists('useAttachment', $options)) {
            $useAttachment = $options['useAttachment'];
        }

        if (array_key_exists('iconEmoji', $options)) {
            $iconEmoji = $options['iconEmoji'];
        }

        if (array_key_exists('useShortAttachment', $options)) {
            $useShortAttachment = $options['useShortAttachment'];
        }

        if (array_key_exists('includeContextAndExtra', $options)) {
            $includeContext = $options['includeContextAndExtra'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('excludeFields', $options)) {
            $excludeFields = $options['excludeFields'];
        }

        try {
            $handler = new SlackWebhookHandler(
                $webhookUrl,
                $channel,
                $userName,
                $useAttachment,
                $iconEmoji,
                $useShortAttachment,
                $includeContext,
                $level,
                $bubble,
                $excludeFields,
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SlackWebhookHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
