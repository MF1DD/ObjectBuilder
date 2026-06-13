<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder;

use InvalidArgumentException;
use ReflectionClass;

class EnumBuilder implements ClassBuilderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var ReflectionClass<object>
     */
    private ReflectionClass $class;

    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): object
    {
        $this->class = $class;
        $this->parameters = $parameters;

        return $this->generateRandomValue();
    }

    private function generateRandomValue(): object
    {
        $enums = array_values($this->class->getName()::cases());

        if ($this->isValideParameter()) {
            $enums = $this->parameters;
            $enum = $enums[array_rand($enums)];

            return constant(sprintf('%s::%s', $this->class->getName(), $enum));
        }

        return $enums[array_rand($enums)];
    }

    private function isValideParameter(): bool
    {
        if (empty($this->parameters)) {
            return false;
        }

        foreach ($this->parameters as $parameter) {
            if (!in_array($parameter, array_column($this->class->getName()::cases(), 'value'), true)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid parameter given vor enum %s: "%s".', $this->class->getShortName(), $parameter)
                );
            }
        }

        return true;
    }
}
