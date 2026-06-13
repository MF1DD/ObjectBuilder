<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\NullBuilder;
use PHPUnit\Framework\TestCase;

class NullBuilderTest extends TestCase
{
    public function testBuildReturnsNull(): void
    {
        $b = new NullBuilder();
        $this->assertNull($b->build());
    }

    public function testBuildAsString(): void
    {
        $b = new NullBuilder();
        $this->assertSame('null', $b->buildAsString());
    }

    public function testSetPropertyDoesNotThrow(): void
    {
        $b = new NullBuilder();
        $p = new Property(name: 'x', type: 'null', value: null);
        $ret = $b->setProperty($p);
        $this->assertInstanceOf(NullBuilder::class, $ret);
    }
}
