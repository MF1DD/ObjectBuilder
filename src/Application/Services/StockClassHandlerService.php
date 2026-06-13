<?php

declare(strict_types=1);

namespace MF1DD\Application\Services;

use ReflectionClass;
use Throwable;
use MF1DD\Domain\StockClassHandlerInterface;
use MF1DD\Infrastructure\StockClass\ArrayObjectHandler;
use MF1DD\Infrastructure\StockClass\DateIntervalHandler;
use MF1DD\Infrastructure\StockClass\DatePeriodHandler;
use MF1DD\Infrastructure\StockClass\DateTimeImmutableHandler;
use MF1DD\Infrastructure\StockClass\ReflectionFunctionHandler;
use MF1DD\Infrastructure\StockClass\SplFileInfoHandler;

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
