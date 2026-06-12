<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass;

use DatePeriod;
use ReflectionClass;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class DatePeriodHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new DatePeriod(
            new \DateTimeImmutable('1983-08-04T00:06:00Z'),
            new \DateInterval('P7D'),
            4,
            \DatePeriod::EXCLUDE_START_DATE,
        );
    }

    public static function supports(ReflectionClass $class): bool
    {
        return $class->getName() === DatePeriod::class;
    }
}
