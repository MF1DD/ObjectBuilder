<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Interface;

use MF1DD\Application\Interface\ThrowableHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ThrowableHandlerTest extends TestCase
{
    public function testExecute(): void
    {
        $handler = new ThrowableHandler();
        $result = $handler->execute(new ReflectionClass(\Throwable::class), []);
        $this->assertInstanceOf(\Throwable::class, $result);
    }
}
