<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity;

class PrivateConstruct
{
    private function __construct(
        private readonly string $name,
        private readonly string $gender,
    ) {
    }

    public static function newMalePerson(string $name): PrivateConstruct
    {
        return new self($name, 'M');
    }

    public static function newFemalePerson(string $name): static
    {
        return new self($name, 'W');
    }

    public static function newOtherPerson(string $name): self
    {
        return new self($name, 'O');
    }

    public static function getAllGender(string $name): array
    {
        return ['M', 'W', 'O'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGender(): string
    {
        return $this->gender;
    }


}
