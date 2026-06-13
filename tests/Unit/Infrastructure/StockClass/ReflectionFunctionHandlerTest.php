<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure\StockClass;

use MF1DD\Infrastructure\StockClass\ReflectionFunctionHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

class ReflectionFunctionHandlerTest extends TestCase
{
    public function testSupports(): void
    {
        $handler = new ReflectionFunctionHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(ReflectionFunction::class)));
    }
}
