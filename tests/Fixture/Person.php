<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

use DateTimeImmutable;
use MF1DD\Tests\Fixture\MyEnum;
use MF1DD\Tests\Fixture\MyInterface;
use MF1DD\Tests\Fixture\MyTrait;

class Person
{
    use MyTrait;

    public function __construct(
        private readonly Name $name,
        private readonly int $age,
        private readonly Address $address,
        private readonly MyEnum $status,
        private readonly DateTimeImmutable $birthDate,
        private readonly MyInterface $someInterface,
    ) {
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
