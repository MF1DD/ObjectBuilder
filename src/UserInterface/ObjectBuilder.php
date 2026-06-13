<?php

declare(strict_types=1);

namespace MF1DD\UserInterface;

use MF1DD\Domain\ClassBuilderInterface;
use MF1DD\Application\Services\ObjectBuildService;

final class ObjectBuilder
{
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
        private readonly string $className,
        private readonly array $parameters,
    ) {
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
        return ObjectBuildService::build(
            $this->className,
            $this->parameters,
            $this->constraints,
            $this->forcedBuilder,
        );
    }
}
