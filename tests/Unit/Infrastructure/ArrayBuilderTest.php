<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\ArrayBuilder;
use PHPUnit\Framework\TestCase;

class ArrayBuilderTest extends TestCase
{
    public function testDefaultValue(): void
    {
        $b = new ArrayBuilder();
        $this->assertSame(['a' => 13], $b->build());
    }

    public function testWithGivenValue(): void
    {
        $b = new ArrayBuilder();
        $b->setProperty(new Property(name: 'x', type: 'array', value: ['foo' => 'bar']));
        $this->assertSame(['foo' => 'bar'], $b->build());
    }

    public function testBuildAsString(): void
    {
        $b = new ArrayBuilder();
        $this->assertIsString($b->buildAsString());
        $this->assertStringContainsString('13', $b->buildAsString());
    }
}
