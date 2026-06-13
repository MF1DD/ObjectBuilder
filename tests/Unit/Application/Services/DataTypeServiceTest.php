<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Services;

use MF1DD\Application\Services\DataTypeService;
use MF1DD\Infrastructure\ArrayBuilder;
use MF1DD\Infrastructure\BooleanBuilder;
use MF1DD\Infrastructure\CallbackBuilder;
use MF1DD\Infrastructure\FloatBuilder;
use MF1DD\Infrastructure\IntegerBuilder;
use MF1DD\Infrastructure\MixedBuilder;
use MF1DD\Infrastructure\NullBuilder;
use MF1DD\Infrastructure\SimpleObjectBuilder;
use MF1DD\Infrastructure\StringBuilder;
use PHPUnit\Framework\TestCase;

class DataTypeServiceTest extends TestCase
{
    public function testGetNullType(): void
    {
        $this->assertNull(DataTypeService::getDataTypeFromString(null));
    }

    public function testGetNullableType(): void
    {
        $result = DataTypeService::getDataTypeFromString('?int');
        $this->assertSame(['?', 'int'], $result);
    }

    public function testGetUnionType(): void
    {
        $result = DataTypeService::getDataTypeFromString('int|string');
        $this->assertSame(['int', 'string'], $result);
    }

    public function testGetIntersectionType(): void
    {
        $result = DataTypeService::getDataTypeFromString('Countable&Iterator');
        $this->assertSame(['Countable', 'Iterator'], $result);
    }

    public function testGetSimpleType(): void
    {
        $result = DataTypeService::getDataTypeFromString('int');
        $this->assertSame(['int'], $result);
    }

    public function testCustomBuilderPersistence(): void
    {
        $custom = new NullBuilder();
        DataTypeService::register('xx-null', $custom);
        $this->assertSame($custom, DataTypeService::getDataTypeBuilder('xx-null'));
        DataTypeService::reset();
        $this->assertNull(DataTypeService::getDataTypeBuilder('xx-null'));
    }

    public function testGetBuilderForInt(): void
    {
        $this->assertInstanceOf(IntegerBuilder::class, DataTypeService::getDataTypeBuilder('int'));
    }

    public function testGetBuilderForFloat(): void
    {
        $this->assertInstanceOf(FloatBuilder::class, DataTypeService::getDataTypeBuilder('float'));
    }

    public function testGetBuilderForString(): void
    {
        $this->assertInstanceOf(StringBuilder::class, DataTypeService::getDataTypeBuilder('string'));
    }

    public function testGetBuilderForBool(): void
    {
        $this->assertInstanceOf(BooleanBuilder::class, DataTypeService::getDataTypeBuilder('bool'));
    }

    public function testGetBuilderForArray(): void
    {
        $this->assertInstanceOf(ArrayBuilder::class, DataTypeService::getDataTypeBuilder('array'));
    }

    public function testGetBuilderForMixed(): void
    {
        $this->assertInstanceOf(MixedBuilder::class, DataTypeService::getDataTypeBuilder('mixed'));
    }

    public function testGetBuilderForObject(): void
    {
        $this->assertInstanceOf(SimpleObjectBuilder::class, DataTypeService::getDataTypeBuilder('object'));
    }

    public function testGetBuilderForCallback(): void
    {
        $this->assertInstanceOf(CallbackBuilder::class, DataTypeService::getDataTypeBuilder('callback'));
    }

    public function testGetBuilderForCallable(): void
    {
        $this->assertInstanceOf(CallbackBuilder::class, DataTypeService::getDataTypeBuilder('callable'));
    }

    public function testGetBuilderForIterable(): void
    {
        $this->assertInstanceOf(ArrayBuilder::class, DataTypeService::getDataTypeBuilder('iterable'));
    }

    public function testGetBuilderForNull(): void
    {
        $this->assertInstanceOf(NullBuilder::class, DataTypeService::getDataTypeBuilder(null));
    }

    public function testGetBuilderForQuestionMark(): void
    {
        $this->assertInstanceOf(NullBuilder::class, DataTypeService::getDataTypeBuilder('?'));
    }

    public function testGetBuilderForNullString(): void
    {
        $this->assertInstanceOf(NullBuilder::class, DataTypeService::getDataTypeBuilder('null'));
    }

    public function testGetBuilderForEmptyString(): void
    {
        $this->assertInstanceOf(NullBuilder::class, DataTypeService::getDataTypeBuilder(''));
    }
}
