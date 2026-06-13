<?php

declare(strict_types=1);

namespace MF1DD\Tests\Infrastructure;

use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;
use MF1DD\Infrastructure\StringBuilder;
use PHPUnit\Framework\TestCase;

class StringBuilderTest extends TestCase
{
    public function testGivenValue(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'x', type: 'string', value: 'hello'));
        $this->assertSame('hello', $b->build());
    }

    public function testBuildAsStringQuotesValue(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'x', type: 'string', value: 'test'));
        $this->assertSame("'test'", $b->buildAsString());
    }

    public function testTimezone(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'timezone', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertContains($result, timezone_identifiers_list());
    }

    public function testDateTimeFormat(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'datetime', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testCountryCode(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'countrycode', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertSame(2, strlen($result));
        $this->assertSame(strtoupper($result), $result);
    }

    public function testEmailPattern(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'email', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^[a-z0-9]+@[a-z]+\.[a-z]+$/', $result);
    }

    public function testPhonePattern(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'phone', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertStringStartsWith('+', $result);
        $this->assertStringContainsString(' ', $result);
    }

    public function testFirstname(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'firstname', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[A-Z]/', $result);
    }

    public function testLastname(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'lastname', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[A-Z]/', $result);
    }

    public function testCity(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'city', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
    }

    public function testStreet(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'street', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertStringContainsString(' ', $result);
    }

    public function testZip(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'zip', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^\d{5}$/', $result);
    }

    public function testFormatEmail(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'email'])
        ));
        $result = $b->build();
        $this->assertStringContainsString('@', $result);
    }

    public function testFormatUrl(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'url'])
        ));
        $result = $b->build();
        $this->assertStringStartsWith('https://', $result);
    }

    public function testFormatUuid(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'uuid'])
        ));
        $result = $b->build();
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
            $result
        );
        $this->assertSame(36, strlen($result));
    }
}
