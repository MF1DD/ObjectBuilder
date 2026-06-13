<?php
declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Interface;

use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;

interface SimpleReturnObjectTestInterface
{
    public function getAddress(): Address;
}
