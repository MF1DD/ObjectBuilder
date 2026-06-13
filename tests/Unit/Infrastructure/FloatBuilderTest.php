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

    public function testWithConstraints(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 1, 'max' => 10])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(1.0, $result);
        $this->assertLessThanOrEqual(10.0, $result);
    }

    public function testMinOnly(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 5])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(5.0, $result);
    }

    public function testMaxOnly(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['max' => 5])
        ));
        $result = $builder->build();
        $this->assertLessThanOrEqual(5.0, $result);
    }

    public function testWithoutConstraints(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(name: 'score', type: 'float', value: new NoValueSet()));
        $result = $builder->build();
        $this->assertIsFloat($result);
    }
}
