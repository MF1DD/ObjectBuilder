<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

class Parameters
{
    /**
     * @param array<string|int, mixed> $parameter
     */
    public function __construct(
        public readonly array $parameter,
    ) {
    }
}
