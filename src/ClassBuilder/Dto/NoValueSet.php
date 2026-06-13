<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Dto;

final class NoValueSet
{
    public function __toString(): string
    {
        return 'null';
    }
}
