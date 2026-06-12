<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\DataTypes;

use InvalidArgumentException;
use Timelesstron\ObjectBuilder\ClassBuilder\Dto\NoValueSet;
use Timelesstron\ObjectBuilder\Dto\Property;

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
        if (!is_string($property->value) && $property->value !== null) {
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
            return $this->generateRandomString(mt_rand(5, 20));
        }

        return match (strtolower($this->property->name)) {
            'timezone' => $this->randomTimezone(),
            'countrycode' => $this->randomCountryCode(),
            'datetime' => $this->randomDateTime(),

            default => $this->generateRandomString(mt_rand(5, 20))
        };
    }

    private function generateRandomString(int $length): string
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
}
