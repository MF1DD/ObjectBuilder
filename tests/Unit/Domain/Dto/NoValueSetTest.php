<?php

declare(strict_types=1);

namespace MF1DD\Tests\Domain\Dto;

use MF1DD\Domain\Dto\NoValueSet;
use PHPUnit\Framework\TestCase;

class NoValueSetTest extends TestCase
{
    public function testToString(): void
    {
        $nvs = new NoValueSet();
        $this->assertSame('null', (string) $nvs);
    }
}
