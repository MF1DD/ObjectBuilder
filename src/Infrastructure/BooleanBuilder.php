<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use InvalidArgumentException;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;

final class BooleanBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    public function build(): bool
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            /** @var bool $value */
            $value = $this->property->value;
            return $value;
        }

        return (bool)mt_rand(0, 1);
    }

    public function setProperty(Property $property): self
    {
        if (!$property->value instanceof NoValueSet && !is_bool($property->value) && $property->value !== null) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" must be an boolean. %s given', $property->value, gettype($property->value))
            );
        }

        $this->property = $property;

        return $this;
    }

    public function buildAsString(): string
    {
        return $this->build() ? 'true' : 'false';
    }
}
