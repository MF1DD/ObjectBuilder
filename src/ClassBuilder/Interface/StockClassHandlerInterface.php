<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface;

use ReflectionClass;
use Throwable;

interface StockClassHandlerInterface
{
    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     */
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object;

    /**
     * @param ReflectionClass<object> $class
     */
    public static function supports(ReflectionClass $class): bool;
}
