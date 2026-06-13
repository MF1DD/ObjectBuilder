<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Services;

use MF1DD\ObjectBuilder\DataTypes\ArrayBuilder;
use MF1DD\ObjectBuilder\DataTypes\BooleanBuilder;
use MF1DD\ObjectBuilder\DataTypes\CallbackBuilder;
use MF1DD\ObjectBuilder\DataTypes\DataTypeInterface;
use MF1DD\ObjectBuilder\DataTypes\FloatBuilder;
use MF1DD\ObjectBuilder\DataTypes\IntegerBuilder;
use MF1DD\ObjectBuilder\DataTypes\MixedBuilder;
use MF1DD\ObjectBuilder\DataTypes\NullBuilder;
use MF1DD\ObjectBuilder\DataTypes\SimpleObjectBuilder;
use MF1DD\ObjectBuilder\DataTypes\StringBuilder;

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
