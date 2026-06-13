<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Services;

use MF1DD\Application\Services\DataTypeService;
use MF1DD\Infrastructure\NullBuilder;
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
}
