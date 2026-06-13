<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use InvalidArgumentException;
use MF1DD\ObjectBuilder\ClassBuilder\Dto\NoValueSet;
use MF1DD\ObjectBuilder\Dto\Property;

class ArrayBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    /**
     * @return array<int|string, mixed>
     */
    public function build(): array
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            return $this->property->value;
        }

        return [
            'a' => 13,
        ];
    }

    public function setProperty(Property $property): self
    {
        if (!$property->value instanceof NoValueSet && !is_array($property->value) && $property->value !== null) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" must be an array. %s given', $property->value, gettype($property->value))
            );
        }

        $this->property = $property;

        return $this;
    }

    public function buildAsString(): string
    {
        return var_export($this->build(), true);
    }
}
