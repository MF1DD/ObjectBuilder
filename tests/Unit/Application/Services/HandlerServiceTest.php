<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Services;

use MF1DD\Application\Interface\ThrowableHandler;
use MF1DD\Application\Services\HandlerService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HandlerServiceTest extends TestCase
{
    public function testGetHandlerForThrowable(): void
    {
        $handler = HandlerService::getHandler(new ReflectionClass(\Throwable::class));
        $this->assertInstanceOf(ThrowableHandler::class, $handler);
    }
}
