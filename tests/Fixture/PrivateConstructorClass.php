<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

class PrivateConstructorClass
{
    private function __construct() {}

    public function current(): void {}
}
