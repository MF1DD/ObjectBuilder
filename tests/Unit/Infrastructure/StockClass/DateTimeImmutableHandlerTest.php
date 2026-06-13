<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure\StockClass;

use DateTime;
use DateTimeImmutable;
use MF1DD\Infrastructure\StockClass\DateTimeImmutableHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DateTimeImmutableHandlerTest extends TestCase
{
    public function testSupports(): void
    {
        $handler = new DateTimeImmutableHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(DateTimeImmutable::class)));
        $this->assertTrue($handler::supports(new ReflectionClass(DateTime::class)));
        $this->assertFalse($handler::supports(new ReflectionClass(\stdClass::class)));
    }
}
