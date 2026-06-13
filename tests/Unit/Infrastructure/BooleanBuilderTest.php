<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\BooleanBuilder;
use PHPUnit\Framework\TestCase;

class BooleanBuilderTest extends TestCase
{
    public function testReturnsBool(): void
    {
        $b = new BooleanBuilder();
        $result = $b->build();
        $this->assertIsBool($result);
    }

    public function testWithGivenValue(): void
    {
        $b = new BooleanBuilder();
        $b->setProperty(new Property(name: 'x', type: 'bool', value: true));
        $this->assertTrue($b->build());
    }

    public function testBuildAsStringMatchesBuild(): void
    {
        $b = new BooleanBuilder();
        $b->setProperty(new Property(name: 'x', type: 'bool', value: true));
        $this->assertSame('true', $b->buildAsString());

        $b2 = new BooleanBuilder();
        $b2->setProperty(new Property(name: 'x', type: 'bool', value: false));
        $this->assertSame('false', $b2->buildAsString());
    }
}
