<?php

declare(strict_types=1);

namespace MF1DD\Application\Services;

use ReflectionClass;
use MF1DD\Domain\ClassBuilderInterface;
use MF1DD\Application\AbstractClassBuilder;
use MF1DD\Application\ClassBuilder;
use MF1DD\Application\EnumBuilder;
use MF1DD\Application\InterfaceBuilder;
use MF1DD\Application\TraitBuilder;

final class ClassBuilderService
{
    /**
     * @param ReflectionClass<object> $reflection
     */
    public static function getClassBuilder(ReflectionClass $reflection): ClassBuilderInterface
    {
        return match (true) {
            $reflection->isEnum() => new EnumBuilder(),
            $reflection->isTrait() => new TraitBuilder(),
            $reflection->isInterface() => new InterfaceBuilder(),
            $reflection->isAbstract() => new AbstractClassBuilder(),
            default => new ClassBuilder(),
        };
    }
}
