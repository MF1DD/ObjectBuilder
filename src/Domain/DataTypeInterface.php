<?php

declare(strict_types=1);

namespace MF1DD\Domain;

use MF1DD\Domain\Dto\Property;

interface DataTypeInterface
{
    public function build(): mixed;

    public function setProperty(Property $property): self;

    public function buildAsString(): string;
}
