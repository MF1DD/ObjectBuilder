<?php

declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

abstract class AbstractVehicle
{
    public function __construct(
        public readonly string $brand,
    ) {
    }

    abstract public function getType(): string;
}
