<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Services;

use Timelesstron\ObjectBuilder\DataTypes\ArrayBuilder;
use Timelesstron\ObjectBuilder\DataTypes\BooleanBuilder;
use Timelesstron\ObjectBuilder\DataTypes\CallbackBuilder;
use Timelesstron\ObjectBuilder\DataTypes\DataTypeInterface;
use Timelesstron\ObjectBuilder\DataTypes\FloatBuilder;
use Timelesstron\ObjectBuilder\DataTypes\IntegerBuilder;
use Timelesstron\ObjectBuilder\DataTypes\MixedBuilder;
use Timelesstron\ObjectBuilder\DataTypes\NullBuilder;
use Timelesstron\ObjectBuilder\DataTypes\SimpleObjectBuilder;
use Timelesstron\ObjectBuilder\DataTypes\StringBuilder;

class DataTypeService
{
    public static function getDataTypeBuilder(?string $type): ?DataTypeInterface
    {
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
