<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Services;

use ReflectionClass;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\FileContentHandler;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\HandlerInterface;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\ImplementedClassHandler;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\ThrowableHandler;
use MF1DD\ObjectBuilder\Exceptions\InterfaceHandlerNotFoundException;

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
