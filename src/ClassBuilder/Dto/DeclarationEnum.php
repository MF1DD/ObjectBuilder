<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Dto;

use InvalidArgumentException;

enum DeclarationEnum: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';
    case PROTECTED = 'protected';

    public static function fromString(mixed $content): self
    {
        return match (true) {
            str_contains($content, ' public ') => self::PUBLIC,
            str_contains($content, ' protected ') => self::PROTECTED,
            str_contains($content, ' private ') => self::PRIVATE,

            default => throw new InvalidArgumentException(sprintf('Invalid declaration provided: %s', $content)),
        };
    }

    public function existDeclaration(DeclarationEnum $declaration): bool
    {
        return in_array($declaration, self::cases(), true);
    }
}
