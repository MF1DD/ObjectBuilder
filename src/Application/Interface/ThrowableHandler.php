<?php

declare(strict_types=1);

namespace MF1DD\Application\Interface;

use Exception;
use ReflectionClass;
use MF1DD\Domain\HandlerInterface;
use MF1DD\Application\Services\ObjectBuildService;

final class ThrowableHandler implements HandlerInterface
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param array<string, mixed> $parameters
     */
    public function execute(ReflectionClass $reflectionClass, array $parameters): object
    {
        return ObjectBuildService::build(Exception::class, [
            ...$parameters,
            'previous' => null,
        ]);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public static function support(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->getName() === 'Throwable';
    }
}
