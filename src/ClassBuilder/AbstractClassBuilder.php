<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder;

use ReflectionClass;
use MF1DD\ObjectBuilder\Exceptions\ObjectBuilderWrongClassesGivenException;
use MF1DD\ObjectBuilder\ObjectBuilder;

class AbstractClassBuilder implements ClassBuilderInterface
{
    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     * @param array<string, array<string, mixed>> $constraints
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): object
    {
        $concreteClasses = [];
        foreach (get_declared_classes() as $className) {
            if (is_subclass_of($className, $class->getName(), true)) {
                $concreteClass = new ReflectionClass($className);
                if (!$concreteClass->isAbstract()) {
                    $concreteClasses[] = $className;
                }
            }
        }

        if (empty($concreteClasses)) {
            throw new ObjectBuilderWrongClassesGivenException(
                sprintf(
                    'No concrete implementation found for abstract class "%s".',
                    $class->getShortName()
                )
            );
        }

        $selectedClass = $concreteClasses[array_rand($concreteClasses)];
        return ObjectBuilder::init($selectedClass, $parameters)->build();
    }
}
