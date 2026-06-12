<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder;

use ReflectionClass;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\ClassBuilderInterface;
use Timelesstron\ObjectBuilder\Exceptions\ObjectBuilderReflectionException;
use Timelesstron\ObjectBuilder\Services\ClassBuilderService;

final class ObjectBuilder
{
    /**
     * @var ReflectionClass<object>
     */
    private ReflectionClass $reflection;

    private ClassBuilderInterface $classBuilder;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $constraints = [];

    /**
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    private function __construct(
        string $className,
        private readonly array $parameters,
    ) {
        $this->reflection = $this->newReflectionClass($className);
        $this->classBuilder = ClassBuilderService::getClassBuilder($this->reflection);
    }

    /**
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    public static function init(string $className, array $parameters = []): self
    {
        return new self($className, $parameters);
    }

    /**
     * @param array<string, mixed> $constraintOptions
     */
    public function with(string $paramName, array $constraintOptions): self
    {
        $this->constraints[$paramName] = $constraintOptions;

        return $this;
    }

    public function build(): object
    {
        return $this->classBuilder->build($this->reflection, $this->parameters, $this->constraints);
    }

    /**
     * @param class-string $className
     *
     * @return ReflectionClass<object>
     */
    public function newReflectionClass(string $className): ReflectionClass
    {
        try {
            return new ReflectionClass($className);
            /** @phpstan-ignore-next-line */
        } catch (Throwable $exception) {
            throw new ObjectBuilderReflectionException($exception);
        }
    }
}
