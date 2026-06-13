<?php

declare(strict_types=1);

namespace MF1DD\Application\Services;

use ReflectionClass;
use MF1DD\Domain\HandlerInterface;
use MF1DD\Application\Interface\FileContentHandler;
use MF1DD\Application\Interface\ImplementedClassHandler;
use MF1DD\Application\Interface\ThrowableHandler;
use MF1DD\Domain\Exceptions\InterfaceHandlerNotFoundException;

final class HandlerService
{
    /**
     * @param ReflectionClass<object> $reflection
     */
    public static function getHandler(ReflectionClass $reflection): HandlerInterface
    {
        return match (true) {
            ThrowableHandler::support($reflection) => new ThrowableHandler(),
            FileContentHandler::support($reflection) => new FileContentHandler(),
            ImplementedClassHandler::support($reflection) => new ImplementedClassHandler(),
            default => throw new InterfaceHandlerNotFoundException()
        };
    }
}
