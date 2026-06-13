<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Interface;

use MF1DD\Application\Interface\FileContentHandler;
use MF1DD\Tests\Helper\EmptyTestInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FileContentHandlerTest extends TestCase
{
    public function testGetReturnType(): void
    {
        $handler = new FileContentHandler();
        $type = $handler->getReturnType('int|string');
        $this->assertContains($type, ['int', 'string']);
    }

    public function testSupport(): void
    {
        $ref = new ReflectionClass(EmptyTestInterface::class);
        $this->assertTrue(FileContentHandler::support($ref));
    }

    public function testSupportReturnsFalseForNonFileClass(): void
    {
        $ref = new ReflectionClass(\stdClass::class);
        $this->assertFalse(FileContentHandler::support($ref));
    }
}
