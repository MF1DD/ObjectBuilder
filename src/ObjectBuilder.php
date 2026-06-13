<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder;

use ReflectionClass;
use Throwable;
use MF1DD\ObjectBuilder\ClassBuilder\ClassBuilderInterface;
use MF1DD\ObjectBuilder\Exceptions\ObjectBuilderReflectionException;
use MF1DD\ObjectBuilder\Services\ClassBuilderService;

final class ObjectBuilder
{
    private ReflectionClass $reflection;

    private ?ClassBuilderInterface $forcedBuilder = null;

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
    }

    /**
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    public static function init(string $className, array $parameters = []): self
    {
        return new self($className, $parameters);
    }

    public function withBuilder(ClassBuilderInterface $builder): self
    {
        $this->forcedBuilder = $builder;

        return $this;
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
        $builder = $this->forcedBuilder ?? ClassBuilderService::getClassBuilder($this->reflection);

        return $builder->build($this->reflection, $this->parameters, $this->constraints);
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
