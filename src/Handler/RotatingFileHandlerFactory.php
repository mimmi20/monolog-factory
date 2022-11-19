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

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\MonologFactory\AddFormatterTrait;
use Mimmi20\MonologFactory\AddProcessorTrait;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

final class RotatingFileHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{filename?: string, maxFiles?: int, level?: (value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::*), bubble?: bool, filePermission?: int|string, useLocking?: bool, dateFormat?: string, filenameFormat?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerExceptionInterface if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): RotatingFileHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('filename', $options)) {
            throw new ServiceNotCreatedException('No filename provided');
        }

        $filename       = $options['filename'];
        $maxFiles       = 0;
        $level          = LogLevel::DEBUG;
        $bubble         = true;
        $filePermission = null;
        $useLocking     = false;
        $filenameFormat = '{filename}-{date}';
        $dateFormat     = RotatingFileHandler::FILE_PER_DAY;

        if (array_key_exists('maxFiles', $options)) {
            $maxFiles = $options['maxFiles'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('filePermission', $options)) {
            $filePermission = (int) $options['filePermission'];
        }

        if (array_key_exists('useLocking', $options)) {
            $useLocking = $options['useLocking'];
        }

        try {
            $handler = new RotatingFileHandler($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', RotatingFileHandler::class),
                0,
                $e,
            );
        }

        if (array_key_exists('filenameFormat', $options) || array_key_exists('dateFormat', $options)) {
            if (array_key_exists('filenameFormat', $options)) {
                $filenameFormat = $options['filenameFormat'];
            }

            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
            }

            $handler->setFilenameFormat($filenameFormat, $dateFormat);
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
