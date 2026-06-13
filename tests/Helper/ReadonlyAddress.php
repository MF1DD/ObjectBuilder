<?php

declare(strict_types=1);

namespace MF1DD\Tests\Helper;

readonly class ReadonlyAddress
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zip = '12345',
    ) {
    }
}
