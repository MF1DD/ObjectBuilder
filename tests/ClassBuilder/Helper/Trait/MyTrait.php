<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Trait;

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