<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use InvalidArgumentException;
use MF1DD\ObjectBuilder\ClassBuilder\Dto\NoValueSet;
use MF1DD\ObjectBuilder\Dto\Property;

class FloatBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    public function build(): float
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            return $this->property->value;
        }

        $min = $this->property?->constraints?->min();
        $max = $this->property?->constraints?->max();
        $minFloat = $min !== null ? (float)$min : 0.0;
        $maxFloat = $max !== null ? (float)$max : ($min !== null ? $minFloat + 1000.0 : 1.0);

        return $minFloat + mt_rand() / mt_getrandmax() * ($maxFloat - $minFloat);
    }

    public function setProperty(Property $property): self
    {
        if (!$property->value instanceof NoValueSet && !is_float($property->value) && $property->value !== null) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" must be an float. %s given', $property->value, gettype($property->value))
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
