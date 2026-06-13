<?php

declare(strict_types=1);

namespace MF1DD\Application\Services;

use MF1DD\Domain\DataTypeInterface;
use MF1DD\Infrastructure\ArrayBuilder;
use MF1DD\Infrastructure\BooleanBuilder;
use MF1DD\Infrastructure\CallbackBuilder;
use MF1DD\Infrastructure\FloatBuilder;
use MF1DD\Infrastructure\IntegerBuilder;
use MF1DD\Infrastructure\MixedBuilder;
use MF1DD\Infrastructure\NullBuilder;
use MF1DD\Infrastructure\SimpleObjectBuilder;
use MF1DD\Infrastructure\StringBuilder;

class DataTypeService
{
    /**
     * @var array<string, DataTypeInterface>
     */
    private static array $customBuilders = [];

    public static function getDataTypeBuilder(?string $type): ?DataTypeInterface
    {
        if ($type !== null && isset(self::$customBuilders[$type])) {
            return self::$customBuilders[$type];
        }

        return match ($type) {
            'int' => new IntegerBuilder(),
            'float' => new FloatBuilder(),
            'string' => new StringBuilder(),
            'bool' => new BooleanBuilder(),
            'array' => new ArrayBuilder(),
            'mixed' => new MixedBuilder(),
            'object' => new SimpleObjectBuilder(),
            'callback', 'callable' => new CallbackBuilder(),
            'iterable' => new ArrayBuilder(),
            null, '?', 'null', '' => new NullBuilder(),

            default => null,
        };
    }

    public static function register(string $type, DataTypeInterface $builder): void
    {
        self::$customBuilders[$type] = $builder;
    }

    public static function reset(): void
    {
        self::$customBuilders = [];
    }

    /**
     * @return array<int, mixed>|null
     */
    public static function getDataTypeFromString(?string $dataType): ?array
    {
        return match (true) {
            $dataType === null => null,
            str_starts_with($dataType, '?') => ['?', ltrim($dataType, '?')], //[array_rand([0, 1])],
            str_contains($dataType, '|') => explode('|', $dataType), //[array_rand(explode('|', $dataType))],
            str_contains($dataType, '&') => explode('&', $dataType),

            default => [$dataType],
        };
    }
}
