<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder;

use ReflectionClass;

interface ClassBuilderInterface
{
    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     * @param array<string, array<string, mixed>> $constraints
     *
     * @return mixed
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): mixed;
}
