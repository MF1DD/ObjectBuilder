<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity;

class Car extends AbstractVehicle
{
    public function getType(): string
    {
        return 'car';
    }
}
