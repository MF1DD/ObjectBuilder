<?php
declare(strict_types=1);

namespace MF1DD\Tests\Helper;

use MF1DD\Tests\Helper\Address;

interface SimpleReturnObjectTestInterface
{
    public function getAddress(): Address;
}
