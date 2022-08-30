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
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

use function array_key_exists;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 * @phpstan-import-type Record from Logger
 */
final class SymfonyMailerHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use SwiftMessageTrait;

    /**
     * @param string                                    $requestedName
     * @param array<string, (string|int|callable)>|null $options
     * @phpstan-param array{mailer?: (bool|string|MailerInterface|TransportInterface), email-template?: (string|Email|callable(string, Record[]): Email), level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): SymfonyMailerHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('mailer', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required mailer class');
        }

        if ($options['mailer'] instanceof MailerInterface || $options['mailer'] instanceof TransportInterface) {
            $mailer = $options['mailer'];
        } elseif (!is_string($options['mailer'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required mailer class');
        } else {
            try {
                $mailer = $container->get($options['mailer']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load mailer class', 0, $e);
            }

            if (!$mailer instanceof MailerInterface && !$mailer instanceof TransportInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', SymfonyMailerHandler::class),
                );
            }
        }

        if (!array_key_exists('email-template', $options)) {
            throw new ServiceNotCreatedException('No Email template provided');
        }

        if (!($options['email-template'] instanceof Email) && !is_callable($options['email-template'])) {
            throw new ServiceNotCreatedException('No Email template provided');
        }

        $emailTemplate = $options['email-template'];

        $level  = LogLevel::DEBUG;
        $bubble = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new SymfonyMailerHandler($mailer, $emailTemplate, $level, $bubble);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
