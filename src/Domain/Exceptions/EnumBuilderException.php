<?php

declare(strict_types=1);

namespace MF1DD\Domain\Exceptions;

use DomainException;

final class EnumBuilderException extends DomainException
{
    /**
     * @param string $message
     */
    public function __construct(
        string $message,
    ) {
        parent::__construct($message);
    }
}
