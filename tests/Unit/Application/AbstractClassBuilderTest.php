<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application;

use MF1DD\Application\AbstractClassBuilder;
use MF1DD\Tests\Helper\AbstractVehicle;
use MF1DD\Tests\Helper\Car;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AbstractClassBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        class_exists(AbstractVehicle::class);
        class_exists(Car::class);

        $builder = new AbstractClassBuilder();
        $result = $builder->build(new ReflectionClass(AbstractVehicle::class), []);
        $this->assertInstanceOf(AbstractVehicle::class, $result);
    }
}
