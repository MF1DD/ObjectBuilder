<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

use InvalidArgumentException;

enum DeclarationEnum: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';
    case PROTECTED = 'protected';

    public static function fromString(mixed $content): self
    {
        return match (true) {
            str_contains((string) $content, ' public ') => self::PUBLIC,
            str_contains((string) $content, ' protected ') => self::PROTECTED,
            str_contains((string) $content, ' private ') => self::PRIVATE,

            default => throw new InvalidArgumentException(sprintf('Invalid declaration provided: %s', $content)),
        };
    }

    public function existDeclaration(DeclarationEnum $declaration): bool
    {
        return in_array($declaration, self::cases(), true);
    }
}
