<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass;

use DateInterval;
use ReflectionClass;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

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
