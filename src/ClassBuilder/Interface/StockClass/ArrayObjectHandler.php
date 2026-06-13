<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Interface\StockClass;

use ArrayObject;
use ReflectionClass;
use Throwable;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class ArrayObjectHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new ArrayObject(['key' => 'value']);
    }

    public static function supports(ReflectionClass $class): bool
    {
        return $class->getName() === ArrayObject::class;
    }
}
