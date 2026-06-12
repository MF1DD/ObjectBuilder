<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Interface;

use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;

interface SimpleReturnObjectTestInterface
{
    public function getAddress(): Address;
}
