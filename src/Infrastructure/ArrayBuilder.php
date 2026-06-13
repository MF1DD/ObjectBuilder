<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use InvalidArgumentException;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;

final class ArrayBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    /**
     * @return array<int|string, mixed>
     */
    public function build(): array
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            /** @var array<int|string, mixed> $value */
            $value = $this->property->value;
            return $value;
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
