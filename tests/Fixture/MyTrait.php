<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

trait MyTrait
{
    public string $trait = 'MyTrait';
    public function toArray(string $jsonString): array
    {
        return json_decode($jsonString, true);
    }

    public function sayHello(): string
    {
        return 'Hello';
    }
}