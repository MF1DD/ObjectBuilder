<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure\StockClass;

use MF1DD\Infrastructure\StockClass\SplFileInfoHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;

class SplFileInfoHandlerTest extends TestCase
{
    public function testSupports(): void
    {
        $handler = new SplFileInfoHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(SplFileInfo::class)));
    }
}
