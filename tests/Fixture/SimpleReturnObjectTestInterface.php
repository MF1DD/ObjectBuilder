<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

use MF1DD\Tests\Fixture\Address;

interface SimpleReturnObjectTestInterface
{
    public function getAddress(): Address;
}
