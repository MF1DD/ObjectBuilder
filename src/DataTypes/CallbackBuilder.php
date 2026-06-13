<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use MF1DD\ObjectBuilder\Dto\Property;

class CallbackBuilder implements DataTypeInterface
{
    public function build(): mixed
    {
        return function ($param1, $param2) {
            return $param1 + $param2;
        };
    }

    public function setProperty(Property $property): self
    {
        return $this;
    }

    public function buildAsString(): string
    {
        return 'function () { return 42; }';
    }
}
