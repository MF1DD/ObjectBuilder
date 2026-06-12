<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Services;

use ReflectionClass;
use Timelesstron\ObjectBuilder\ClassBuilder\AbstractClassBuilder;
use Timelesstron\ObjectBuilder\ClassBuilder\ClassBuilder;
use Timelesstron\ObjectBuilder\ClassBuilder\ClassBuilderInterface;
use Timelesstron\ObjectBuilder\ClassBuilder\EnumBuilder;
use Timelesstron\ObjectBuilder\ClassBuilder\InterfaceBuilder;
use Timelesstron\ObjectBuilder\ClassBuilder\TraitBuilder;

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
