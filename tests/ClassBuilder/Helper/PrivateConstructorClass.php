<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper;

class PrivateConstructorClass
{
    private function __construct() {}

    public function current(): void {}
}
