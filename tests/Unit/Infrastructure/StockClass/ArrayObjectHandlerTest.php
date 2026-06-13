<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure\StockClass;

use MF1DD\Infrastructure\StockClass\ArrayObjectHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ArrayObjectHandlerTest extends TestCase
{
    public function testSupports(): void
    {
        $handler = new ArrayObjectHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(\ArrayObject::class)));
        $this->assertFalse($handler::supports(new ReflectionClass(\stdClass::class)));
    }
}
