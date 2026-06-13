<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use MF1DD\ObjectBuilder\Dto\Property;

class NullBuilder implements DataTypeInterface
{
    public function build(): mixed
    {
        return null;
    }

    public function setProperty(Property $property): self
    {
        return $this;
    }

    public function buildAsString(): string
    {
        return 'null';
    }
}
