<?php
declare(strict_types=1);

namespace MF1DD\Tests\Helper;

trait MyTestTrait
{
    public string $publicTraitProperty = 'Trait property';
    private string $privateTraitProperty = 'Trait property';
    public function publicMethod(): string
    {
        return "This is a public methode from the trait.";
    }
    private function privateMethod(): string
    {
        return "This is a privat methode from the trait.";
    }
    public function callPrivateMethod(): string
    {
        return $this->privateMethod();
    }

    public function getTraitProperty(): string
    {
        return $this->privateTraitProperty;
    }

    public function setTraitProperty($value): void
    {
        $this->privateTraitProperty = $value;
    }
}
