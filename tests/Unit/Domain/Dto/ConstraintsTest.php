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

    public function testAllGetters(): void
    {
        $c = new Constraints(['min' => 1, 'max' => 10, 'length' => 5, 'format' => 'email', 'min_length' => 3, 'max_length' => 20]);
        $this->assertSame(1, $c->min());
        $this->assertSame(10, $c->max());
        $this->assertSame(5, $c->length());
        $this->assertSame('email', $c->format());
        $this->assertSame(3, $c->minLength());
        $this->assertSame(20, $c->maxLength());
    }

    public function testEmptyReturnsNull(): void
    {
        $c = new Constraints([]);
        $this->assertNull($c->min());
        $this->assertNull($c->max());
        $this->assertNull($c->length());
        $this->assertNull($c->format());
    }
}
