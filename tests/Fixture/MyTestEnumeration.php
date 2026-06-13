<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

enum MyTestEnumeration: string
{
    case OK = 'OK';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
}
