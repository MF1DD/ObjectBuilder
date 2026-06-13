<?php
declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests\ClassBuilder;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use MF1DD\ObjectBuilder\ClassBuilder\TraitBuilder;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\MyTestTrait;

class TraitBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        /** @var MyTestTrait $result */
        $result = (new TraitBuilder())->build(
            new ReflectionClass(MyTestTrait::class),
            []
        );

        $this->assertSame('Trait property', $result->publicTraitProperty);
        $this->assertSame('This is a public methode from the trait.', $result->publicMethod());
        $this->assertSame('This is a privat methode from the trait.', $result->callPrivateMethod());
        $this->assertSame('Trait property', $result->getTraitProperty());
        $result->setTraitProperty('aaa');
        $this->assertSame('aaa', $result->getTraitProperty());
    }
}
