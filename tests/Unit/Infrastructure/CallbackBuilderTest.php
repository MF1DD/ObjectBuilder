<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Infrastructure\CallbackBuilder;
use PHPUnit\Framework\TestCase;

class CallbackBuilderTest extends TestCase
{
    public function testReturnsCallable(): void
    {
        $b = new CallbackBuilder();
        $fn = $b->build();
        $this->assertIsCallable($fn);
        $this->assertSame(5, $fn(2, 3));
    }

    public function testBuildAsString(): void
    {
        $b = new CallbackBuilder();
        $this->assertSame('function () { return 42; }', $b->buildAsString());
    }
}
