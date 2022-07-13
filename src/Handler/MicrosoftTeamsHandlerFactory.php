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

use Actived\MicrosoftTeamsNotifier\Handler\MicrosoftTeamsHandler;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function extension_loaded;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class MicrosoftTeamsHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{url?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MicrosoftTeamsHandler
    {
        if (!extension_loaded('curl')) {
            throw new ServiceNotCreatedException(
                sprintf('The curl extension is needed to use the %s', MicrosoftTeamsHandler::class)
            );
        }

        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('url', $options)) {
            throw new ServiceNotCreatedException('No url provided');
        }

        $url     = $options['url'];
        $level   = LogLevel::DEBUG;
        $title   = 'Message';
        $subject = 'Date';
        $emoji   = null;
        $color   = null;
        $format  = '%message%';
        $bubble  = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('title', $options)) {
            $title = $options['title'];
        }

        if (array_key_exists('subject', $options)) {
            $subject = $options['subject'];
        }

        if (array_key_exists('emoji', $options)) {
            $emoji = $options['emoji'];
        }

        if (array_key_exists('color', $options)) {
            $color = $options['color'];
        }

        if (array_key_exists('format', $options)) {
            $format = $options['format'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new MicrosoftTeamsHandler(
            $url,
            $level,
            $title,
            $subject,
            $emoji,
            $color,
            $format,
            $bubble
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
