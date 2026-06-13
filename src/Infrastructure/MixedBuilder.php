<?php

declare(strict_types=1);

namespace MF1DD\Infrastructure;

use DateTimeImmutable;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Property;

class MixedBuilder implements DataTypeInterface
{
    private ?Property $property = null;

    public function build(): mixed
    {
        if ($this->property instanceof Property && !$this->property->value instanceof NoValueSet) {
            return $this->property->value;
        }

        return $this->generateRandomValue();
    }

    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function buildAsString(): string
    {
        return var_export($this->build(), true);
    }

    private function generateRandomValue(): mixed
    {
        $randomArray = [
            mt_rand(),
            mt_rand() / mt_getrandmax(),
            $this->generateRandomString(mt_rand(5, 15)),
            (bool)mt_rand(0, 1),
            $this->generateRandomDateTime(),
            [],
            null,
        ];

        return $randomArray[mt_rand(0, count($randomArray) - 1)];
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

    private function generateRandomDateTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('@' . mt_rand(1_704_067_200, time()));
    }
}
