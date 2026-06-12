<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Interface;

use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;

interface MyInterface
{
    // Konstanten
    public const CONSTANT = 'slkdf';
    public const CONSTANT_TWO = 'value_two';

    public function __construct();
    public function get(string $url): Address;
    public function post(): Address;

    public static function put(): Selectable;
    public static function a(string $url): int;
    public static function b(string $url): float;
    public static function n(string $url): bool;

}