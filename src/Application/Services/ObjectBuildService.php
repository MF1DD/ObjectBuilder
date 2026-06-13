<?php

declare(strict_types=1);

namespace MF1DD\Application\Services;

use ReflectionClass;
use Throwable;
use MF1DD\Domain\ClassBuilderInterface;
use MF1DD\Domain\Exceptions\ObjectBuilderReflectionException;

final class ObjectBuildService
{
    /**
     * @param class-string $className
     * @param array<string, mixed> $parameters
     * @param array<string, array<string, mixed>> $constraints
     * @param ClassBuilderInterface|null $forcedBuilder
     */
    public static function build(
        string $className,
        array $parameters = [],
        array $constraints = [],
        ?ClassBuilderInterface $forcedBuilder = null,
    ): object {
        try {
            $reflection = new ReflectionClass($className);
        } catch (Throwable $exception) {
            throw new ObjectBuilderReflectionException($exception);
        }

        $builder = $forcedBuilder ?? ClassBuilderService::getClassBuilder($reflection);

        return $builder->build($reflection, $parameters, $constraints);
    }
}
