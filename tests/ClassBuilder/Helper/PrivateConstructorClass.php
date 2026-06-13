<?php
declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper;

class PrivateConstructorClass
{
    private function __construct() {}

    public function current(): void {}
}
