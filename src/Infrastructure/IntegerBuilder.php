<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use InvalidArgumentException;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;

class IntegerBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    public function build(): int
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            return $this->property->value;
        }

        $min = $this->property?->constraints?->min();
        $max = $this->property?->constraints?->max();

        if ($min !== null && $max !== null) {
            return mt_rand($min, $max);
        }
        if ($min !== null) {
            return mt_rand($min, $min + 1000);
        }
        if ($max !== null) {
            return mt_rand(0, $max);
        }

        return mt_rand();
    }

    public function setProperty(Property $property): self
    {
        if (!$property->value instanceof NoValueSet && !is_int($property->value) && $property->value !== null) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" must be an integer. %s given', $property->value, gettype($property->value))
            );
        }

        $this->property = $property;

        return $this;
    }

    public function buildAsString(): string
    {
        return (string)$this->build();
    }
}
