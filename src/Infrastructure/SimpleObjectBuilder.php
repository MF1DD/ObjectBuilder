<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\Property;

final class SimpleObjectBuilder implements DataTypeInterface
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
