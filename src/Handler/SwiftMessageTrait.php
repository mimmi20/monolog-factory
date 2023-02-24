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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Swift_Message;

use function is_callable;
use function sprintf;

trait SwiftMessageTrait
{
    /**
     * @phpstan-param (string|Swift_Message|callable(): Swift_Message) $message
     *
     * @phpstan-return (Swift_Message|callable(): Swift_Message)
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getSwiftMessage(
        ContainerInterface $container,
        callable | string | Swift_Message $message,
    ): callable | Swift_Message {
        if (empty($message)) {
            throw new ServiceNotCreatedException(
                'No message service name or callback provided',
            );
        }

        if (is_callable($message) || $message instanceof Swift_Message) {
            return $message;
        }

        if (!$container->has($message)) {
            throw new ServiceNotFoundException(
                'No Message service found',
            );
        }

        try {
            return $container->get($message);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load service %s', $message),
                0,
                $e,
            );
        }
    }
}
