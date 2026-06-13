<?php

declare(strict_types=1);

namespace MF1DD\Domain\Exceptions;

use DomainException;

final class ObjectBuilderNoDependenciesFoundException extends DomainException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
