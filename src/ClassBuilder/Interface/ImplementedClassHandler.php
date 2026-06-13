<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Interface;

use ReflectionClass;

class ImplementedClassHandler implements HandlerInterface
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param array<string, mixed> $parameters
     */
    public function execute(ReflectionClass $reflectionClass, array $parameters): object
    {
        $implementedClasses = $this->interfaceImplementedClasses($reflectionClass);

        return new $implementedClasses[array_rand($implementedClasses)]();
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public static function support(ReflectionClass $reflectionClass): bool
    {
        foreach (get_declared_classes() as $className) {
            if (in_array($reflectionClass->getName(), class_implements($className), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return array<int, string>
     */
    private function interfaceImplementedClasses(ReflectionClass $reflectionClass): array
    {
        $implementingClasses = [];
        foreach (get_declared_classes() as $className) {
            if (in_array($reflectionClass->getName(), class_implements($className), true)) {
                $implementingClasses[] = $className;
            }
        }

        return $implementingClasses;
    }
}
