<?php

declare(strict_types=1);

namespace MF1DD\Tests\Domain\Dto;

use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\Property;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testWithConstraints(): void
    {
        $constraints = new Constraints(['min' => 5, 'max' => 100]);
        $p = new Property(name: 'test', type: 'int', value: 42, constraints: $constraints);
        $this->assertSame('test', $p->name);
        $this->assertSame('int', $p->type);
        $this->assertSame(42, $p->value);
        $this->assertSame(5, $p->constraints?->min());
    }
}
