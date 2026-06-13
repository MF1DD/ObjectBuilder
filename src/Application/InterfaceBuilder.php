<?php

declare(strict_types=1);

namespace MF1DD\Application;

use ReflectionClass;
use MF1DD\Domain\ClassBuilderInterface;
use MF1DD\Application\Services\HandlerService;

class InterfaceBuilder implements ClassBuilderInterface
{
    public const int MAX_ALLOWED_INFINITY_INTERFACE_LOADER = 5;

    private static int $counter = 0;

    public static function counter(): int
    {
        return ++self::$counter;
    }

    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): object
    {
        $interfaceHandler = HandlerService::getHandler($class);

        return $interfaceHandler->execute($class, $parameters);
    }
}
