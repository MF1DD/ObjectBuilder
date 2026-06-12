<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity;

abstract class AbstractVehicle
{
    public function __construct(
        public readonly string $brand,
    ) {
    }

    abstract public function getType(): string;
}
