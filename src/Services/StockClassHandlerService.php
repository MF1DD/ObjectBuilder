<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Services;

use ReflectionClass;
use Throwable;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\ArrayObjectHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\DateIntervalHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\DatePeriodHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\DateTimeImmutableHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\ReflectionFunctionHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClass\SplFileInfoHandler;
use Timelesstron\ObjectBuilder\ClassBuilder\Interface\StockClassHandlerInterface;

final class StockClassHandlerService
{
    /**
     * @var array<int, StockClassHandlerInterface>
     */
    private static array $handlers = [];

    /**
     * @return array<int, StockClassHandlerInterface>
     */
    public static function getHandlers(): array
    {
        if (empty(self::$handlers)) {
            self::$handlers = [
                new DateIntervalHandler(),
                new DatePeriodHandler(),
                new DateTimeImmutableHandler(),
                new ReflectionFunctionHandler(),
                new ArrayObjectHandler(),
                new SplFileInfoHandler(),
            ];
        }

        return self::$handlers;
    }

    /**
     * @param array<int, StockClassHandlerInterface> $handlers
     */
    public static function setHandlers(array $handlers): void
    {
        self::$handlers = $handlers;
    }

    public static function register(StockClassHandlerInterface $handler): void
    {
        self::$handlers[] = $handler;
    }

    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     * @return object|null
     */
    public static function handle(ReflectionClass $class, array $parameters, Throwable $previousException): ?object
    {
        foreach (self::getHandlers() as $handler) {
            if ($handler::supports($class)) {
                return $handler->build($class, $parameters, $previousException);
            }
        }

        return null;
    }
}
