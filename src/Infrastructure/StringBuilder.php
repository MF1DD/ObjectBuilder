<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use InvalidArgumentException;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;

class StringBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    public function build(): string
    {
        if ($this->property !== null && !$this->property->value instanceof NoValueSet) {
            return $this->property->value;
        }

        return $this->createValue();
    }

    public function setProperty(Property $property): self
    {
        if (!$property->value instanceof NoValueSet && !is_string($property->value) && $property->value !== null) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" must be an string. %s given', $property->value, gettype($property->value))
            );
        }

        $this->property = $property;

        return $this;
    }

    public function buildAsString(): string
    {
        return var_export($this->build(), true);
    }

    private function createValue(): string
    {
        if ($this->property === null) {
            return $this->generateAlphanumericString(mt_rand(5, 20));
        }

        $minLen = $this->property->constraints?->minLength() ?? 5;
        $maxLen = $this->property->constraints?->maxLength() ?? 20;
        $length = mt_rand($minLen, $maxLen);

        return match (true) {
            $this->property->constraints?->format() === 'email' => $this->randomEmail(),
            $this->property->constraints?->format() === 'url' => $this->randomUrl(),
            $this->property->constraints?->format() === 'uuid' => $this->randomUuid(),
            strtolower((string) $this->property->name) === 'timezone' => $this->randomTimezone(),
            strtolower((string) $this->property->name) === 'countrycode' => $this->randomCountryCode(),
            strtolower((string) $this->property->name) === 'datetime' => $this->randomDateTime(),
            strtolower((string) $this->property->name) === 'email' => $this->randomEmail(),
            strtolower((string) $this->property->name) === 'url' => $this->randomUrl(),
            strtolower((string) $this->property->name) === 'uuid' => $this->randomUuid(),
            strtolower((string) $this->property->name) === 'phone' => $this->randomPhone(),
            strtolower((string) $this->property->name) === 'firstname' => $this->randomFirstName(),
            strtolower((string) $this->property->name) === 'lastname' => $this->randomLastName(),
            strtolower((string) $this->property->name) === 'city' => $this->randomCity(),
            strtolower((string) $this->property->name) === 'street' => $this->randomStreet(),
            strtolower((string) $this->property->name) === 'zip' => $this->randomPostalCode(),
            strtolower((string) $this->property->name) === 'postcode' => $this->randomPostalCode(),
            default => $this->generateAlphanumericString($length),
        };
    }

    private function generateAlphanumericString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    private function randomTimezone(): string
    {
        $timezones = timezone_identifiers_list();

        return $timezones[array_rand($timezones)];
    }

    /**
     * @info https://packagist.org/packages/aminkhoshzahmat/country-code
     */
    private function randomCountryCode(): string
    {
        $countries = [
            'AF', 'AL', 'DZ', 'AR', 'AU', 'AT', 'BD', 'BE', 'BR', 'BG',
            'CA', 'CL', 'CN', 'CO', 'HR', 'CZ', 'DK', 'EG', 'FI', 'FR',
            'DE', 'GR', 'HK', 'HU', 'IN', 'ID', 'IR', 'IE', 'IL', 'IT',
            'JP', 'KE', 'LU', 'MY', 'MX', 'MA', 'NL', 'NZ', 'NG', 'NO',
            'PK', 'PE', 'PH', 'PL', 'PT', 'RO', 'RU', 'SA', 'RS', 'SG',
            'ZA', 'KR', 'ES', 'SE', 'CH', 'TW', 'TH', 'TR', 'UA', 'AE',
            'GB', 'US', 'VN',
        ];

        return $countries[array_rand($countries)];
    }

    private function randomDateTime(): string
    {
        return date('Y-m-d', mt_rand(strtotime('-1 year'), strtotime('now')));
    }

    private function randomEmail(): string
    {
        $domains = ['example.com', 'test.org', 'mail.net', 'demo.io'];
        return sprintf(
            '%s@%s',
            strtolower($this->generateAlphanumericString(mt_rand(5, 10))),
            $domains[array_rand($domains)]
        );
    }

    private function randomUrl(): string
    {
        return sprintf(
            'https://%s.%s/%s',
            strtolower($this->generateAlphanumericString(mt_rand(4, 8))),
            ['com', 'org', 'net', 'io'][array_rand([0, 1, 2, 3])],
            strtolower($this->generateAlphanumericString(mt_rand(3, 8)))
        );
    }

    private function randomUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function randomPhone(): string
    {
        return sprintf('+%d %d %d', mt_rand(1, 99), mt_rand(100, 999), mt_rand(1000000, 9999999));
    }

    private function randomFirstName(): string
    {
        $names = ['James', 'Mary', 'Robert', 'Patricia', 'John', 'Jennifer', 'Michael', 'Linda', 'David', 'Barbara', 'William', 'Elizabeth', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen'];
        return $names[array_rand($names)];
    }

    private function randomLastName(): string
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        return $names[array_rand($names)];
    }

    private function randomCity(): string
    {
        $cities = ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Leipzig', 'Dortmund', 'Essen', 'London', 'Paris', 'Madrid', 'Rome', 'Amsterdam', 'Vienna', 'Prague', 'Warsaw', 'Budapest', 'Brussels'];
        return $cities[array_rand($cities)];
    }

    private function randomStreet(): string
    {
        $names = ['Main', 'Oak', 'Maple', 'Cedar', 'Pine', 'Elm', 'Washington', 'Park', 'Lake', 'Hill', 'Broadway', 'First', 'Second', 'Third', 'Church', 'Market', 'High', 'Spring', 'West', 'North'];
        $types = ['Street', 'Avenue', 'Road', 'Lane', 'Drive', 'Boulevard', 'Way', 'Court', 'Place', 'Circle'];
        return sprintf('%s %s', $names[array_rand($names)], $types[array_rand($types)]);
    }

    private function randomPostalCode(): string
    {
        return (string)mt_rand(10000, 99999);
    }
}
