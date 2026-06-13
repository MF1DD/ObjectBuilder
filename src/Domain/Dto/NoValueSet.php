<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

final class NoValueSet implements \Stringable
{
    public function __toString(): string
    {
        return 'null';
    }
}
