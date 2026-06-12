<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity;

readonly class ReadonlyPerson
{
    public function __construct(
        public string $name,
        public int $age,
        public ?ReadonlyAddress $address = null,
    ) {
    }
}
