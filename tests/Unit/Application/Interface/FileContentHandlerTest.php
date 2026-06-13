<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Interface;

use MF1DD\Application\Interface\FileContentHandler;
use PHPUnit\Framework\TestCase;

class FileContentHandlerTest extends TestCase
{
    public function testGetReturnType(): void
    {
        $handler = new FileContentHandler();
        $type = $handler->getReturnType('int|string');
        $this->assertContains($type, ['int', 'string']);
    }
}
