<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use MF1DD\ObjectBuilder\Dto\Property;

class SimpleObjectBuilder implements DataTypeInterface
{
    public function build(): mixed
    {
        return (object)[];
    }

    public function setProperty(Property $property): self
    {
        return $this;
    }

    public function buildAsString(): string
    {
        return '(object)[]';
    }
}
