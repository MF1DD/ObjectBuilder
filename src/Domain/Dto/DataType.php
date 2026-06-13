<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

use MF1DD\Domain\Exceptions\ObjectBuilderWrongClassesGivenException;

final class DataType
{
    public readonly bool $isObject;

    public function __construct(
        public readonly ?string $namespace,
        public readonly mixed $type,
        public readonly mixed $value,
        public readonly bool $isValueGiven,
    ) {
        $this->isObject = is_object($value);

        if ($this->isObject && !str_ends_with($value::class, (string) $type)) {
            throw new ObjectBuilderWrongClassesGivenException(
                sprintf('Given wrong class for return type. Given: %s. Expected: %s', $value::class, $type),
            );
        }
    }
}
