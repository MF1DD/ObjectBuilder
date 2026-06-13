<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure\StockClass;

use DateInterval;
use ReflectionClass;
use Throwable;
use MF1DD\Domain\StockClassHandlerInterface;

final class DateIntervalHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new DateInterval('P7D');
    }

    public static function supports(ReflectionClass $class): bool
    {
        return $class->getName() === DateInterval::class;
    }
}
