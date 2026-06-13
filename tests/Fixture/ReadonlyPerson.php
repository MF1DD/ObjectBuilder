<?php

declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

readonly class ReadonlyPerson
{
    public function __construct(
        public string $name,
        public int $age,
        public ?ReadonlyAddress $address = null,
    ) {
    }
}
