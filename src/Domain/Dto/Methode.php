<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

final class Methode
{
    public function __construct(
        public readonly string $content,
        public readonly DeclarationEnum $declaration,
        public readonly bool $isStatic,
        public readonly string $name,
        public readonly Parameters $parameters,
        public readonly ?DataType $returnValue,
    ) {
    }
}
