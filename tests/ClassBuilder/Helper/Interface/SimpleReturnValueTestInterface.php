<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Interface;

interface SimpleReturnValueTestInterface
{
    public function __construct();

    public function getArray(): array;
    public function getInt(): int;
    public function getString(): string;
    public function getFloat(): float;
    public function getBool(): bool;
    public function getMixed(): mixed;
    public function getRandom(): int|string;
}
