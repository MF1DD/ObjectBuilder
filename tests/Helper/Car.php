<?php

declare(strict_types=1);

namespace MF1DD\Tests\Helper;

class Car extends AbstractVehicle
{
    public function getType(): string
    {
        return 'car';
    }
}
