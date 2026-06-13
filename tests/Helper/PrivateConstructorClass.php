<?php
declare(strict_types=1);

namespace MF1DD\Tests\Helper;

class PrivateConstructorClass
{
    private function __construct() {}

    public function current(): void {}
}
