<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\ClassBuilder\Interface\StockClass;

use SplFileInfo;
use ReflectionClass;
use Throwable;
use MF1DD\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class SplFileInfoHandler implements StockClassHandlerInterface
{
    public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
    {
        return new SplFileInfo(__FILE__);
    }

    public static function supports(ReflectionClass $class): bool
    {
        return $class->getName() === SplFileInfo::class;
    }
}
