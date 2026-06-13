<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\DataTypes;

use MF1DD\ObjectBuilder\Dto\Property;

interface DataTypeInterface
{
    public function build(): mixed;

    public function setProperty(Property $property): self;

    public function buildAsString(): string;
}
