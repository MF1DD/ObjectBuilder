<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Infrastructure\SimpleObjectBuilder;
use PHPUnit\Framework\TestCase;

class SimpleObjectBuilderTest extends TestCase
{
    public function testReturnsObject(): void
    {
        $b = new SimpleObjectBuilder();
        $obj = $b->build();
        $this->assertIsObject($obj);
        $this->assertSame([], (array) $obj);
    }
}
