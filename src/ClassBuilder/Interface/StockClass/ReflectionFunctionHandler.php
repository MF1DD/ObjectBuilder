<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass;

use ReflectionClass;
use ReflectionFunction;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class ReflectionFunctionHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new ReflectionFunction('strlen');
    }

    public static function supports(ReflectionClass $class): bool
    {
        return $class->getName() === ReflectionFunction::class;
    }
}
