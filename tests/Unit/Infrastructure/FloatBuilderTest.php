<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\FloatBuilder;
use PHPUnit\Framework\TestCase;

class FloatBuilderTest extends TestCase
{
    public function testSetPropertyAcceptsNoValueSet(): void
    {
        $b = new FloatBuilder();
        $b->setProperty(new Property(name: 'x', type: 'float', value: new NoValueSet()));
        $result = $b->build();
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0.0, $result);
    }

    public function testWithConstraintsBothEnds(): void
    {
        $b = new FloatBuilder();
        $b->setProperty(new Property(
            name: 'x', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 10, 'max' => 10])
        ));
        $this->assertSame(10.0, $b->build());
    }
}
