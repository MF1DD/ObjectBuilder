<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\IntegerBuilder;
use PHPUnit\Framework\TestCase;

class IntegerBuilderTest extends TestCase
{
    public function testSetPropertyAcceptsNoValueSet(): void
    {
        $b = new IntegerBuilder();
        $b->setProperty(new Property(name: 'x', type: 'int', value: new NoValueSet()));
        $result = $b->build();
        $this->assertIsInt($result);
    }

    public function testWithFloatConstraintCast(): void
    {
        $b = new IntegerBuilder();
        $b->setProperty(new Property(
            name: 'x', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => '5', 'max' => '10'])
        ));
        $result = $b->build();
        $this->assertGreaterThanOrEqual(5, $result);
        $this->assertLessThanOrEqual(10, $result);
    }

    public function testWithMinMaxConstraints(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => 18, 'max' => 65])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(18, $result);
        $this->assertLessThanOrEqual(65, $result);
    }

    public function testMinOnly(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => 100])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(100, $result);
    }

    public function testMaxOnly(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['max' => 50])
        ));
        $result = $builder->build();
        $this->assertLessThanOrEqual(50, $result);
    }

    public function testWithoutConstraints(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(name: 'age', type: 'int', value: new NoValueSet()));
        $result = $builder->build();
        $this->assertIsInt($result);
    }
}
