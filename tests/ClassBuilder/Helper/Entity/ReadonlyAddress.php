<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity;

readonly class ReadonlyAddress
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zip = '12345',
    ) {
    }
}
