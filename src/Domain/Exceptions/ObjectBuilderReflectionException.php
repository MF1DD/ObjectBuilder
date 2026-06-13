<?php

declare(strict_types=1);

namespace MF1DD\Domain\Exceptions;

use DomainException;
use Throwable;

final class ObjectBuilderReflectionException extends DomainException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            $previous?->getMessage() ?? 'Reflection error',
            (int) ($previous?->getCode() ?? 0),
            $previous
        );
    }
}
