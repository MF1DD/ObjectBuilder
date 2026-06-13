<?php

declare(strict_types=1);

namespace MF1DD\Tests\Domain\Dto;

use MF1DD\Domain\Dto\Constraints;
use PHPUnit\Framework\TestCase;

class ConstraintsTest extends TestCase
{
    public function testMinMaxLength(): void
    {
        $c = new Constraints(['min' => 0, 'max' => 0, 'min_length' => 1, 'max_length' => 1]);
        $this->assertSame(0, $c->min());
        $this->assertSame(0, $c->max());
        $this->assertSame(1, $c->minLength());
        $this->assertSame(1, $c->maxLength());
        $this->assertNull($c->format());
        $this->assertNull($c->length());
    }
}
