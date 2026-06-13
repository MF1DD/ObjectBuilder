<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\Property;

final class CallbackBuilder implements DataTypeInterface
{
    public function build(): mixed
    {
        return fn(int $param1, int $param2): int => $param1 + $param2;
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
