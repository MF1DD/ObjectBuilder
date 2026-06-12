<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass;

use DateTime;
use DateTimeImmutable;
use ReflectionClass;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class DateTimeImmutableHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new DateTimeImmutable('@' . mt_rand(1_704_067_200, time()));
    }

    public static function supports(ReflectionClass $class): bool
    {
        return in_array($class->getName(), [DateTime::class, DateTimeImmutable::class], true);
    }
}
