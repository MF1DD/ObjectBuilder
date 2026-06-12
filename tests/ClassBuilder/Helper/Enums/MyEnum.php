<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Enums;

enum MyEnum: string
{
    case OK = 'OK';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
}
