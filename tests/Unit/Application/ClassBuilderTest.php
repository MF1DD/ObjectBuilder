<?php
declare(strict_types=1);

namespace MF1DD\Tests\Application;

use PHPUnit\Framework\TestCase;
use MF1DD\Application\ClassBuilder;
use MF1DD\Domain\Exceptions\ObjectBuilderWrongClassesGivenException;
use MF1DD\UserInterface\ObjectBuilder;
use MF1DD\Tests\Helper\Address;
use MF1DD\Tests\Helper\PrivateConstruct;
use MF1DD\Tests\Helper\PrivateConstructorClass;

class ClassBuilderTest extends TestCase
{
    public function testClassWithPrivatConstructorThrowAnException(): void
    {
        $this->expectException(ObjectBuilderWrongClassesGivenException::class);
        $this->expectExceptionMessage('Cannot handle class "PrivateConstructorClass" with private constructor and no static methode.');

        ObjectBuilder::init(PrivateConstructorClass::class)->build();
    }

    public function testSimpleClass(): void
    {
        $address = ObjectBuilder::init(Address::class)->build();
        $this->assertInstanceOf(Address::class, $address);
    }

    public function testSimpleClassWithoutConstructor(): void
    {
        $person = ObjectBuilder::init(PrivateConstruct::class)->build();
        $this->assertInstanceOf(PrivateConstruct::class, $person);
    }
}
