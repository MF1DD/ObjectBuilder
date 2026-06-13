<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

class Address
{
    public function __construct(
        private readonly mixed $street,
        private readonly string|int $zip,
        private readonly string $city,
        private readonly ?string $country,
        private readonly bool $mainResidence,
    ) {
    }

    public function getStreet(): mixed
    {
        return $this->street;
    }

    public function getZip(): int|string
    {
        return $this->zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function isMainResidence(): bool
    {
        return $this->mainResidence;
    }

}
