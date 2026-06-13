<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Services;

use ReflectionClass;
use MF1DD\ObjectBuilder\ClassBuilder\AbstractClassBuilder;
use MF1DD\ObjectBuilder\ClassBuilder\ClassBuilder;
use MF1DD\ObjectBuilder\ClassBuilder\ClassBuilderInterface;
use MF1DD\ObjectBuilder\ClassBuilder\EnumBuilder;
use MF1DD\ObjectBuilder\ClassBuilder\InterfaceBuilder;
use MF1DD\ObjectBuilder\ClassBuilder\TraitBuilder;

class ClassBuilderService
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
