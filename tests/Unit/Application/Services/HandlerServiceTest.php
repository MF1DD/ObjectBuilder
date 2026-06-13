<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Services;

use MF1DD\Application\Interface\FileContentHandler;
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

    public function testGetHandlerForFileBasedInterface(): void
    {
        $handler = HandlerService::getHandler(new ReflectionClass(\MF1DD\Tests\Helper\SimpleTestInterface::class));
        $this->assertInstanceOf(FileContentHandler::class, $handler);
    }
}
