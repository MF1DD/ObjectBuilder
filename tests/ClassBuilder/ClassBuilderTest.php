<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder;

use PHPUnit\Framework\TestCase;
use Timelesstron\ObjectBuilder\ClassBuilder\ClassBuilder;
use Timelesstron\ObjectBuilder\Exceptions\ObjectBuilderWrongClassesGivenException;
use Timelesstron\ObjectBuilder\ObjectBuilder;
use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;
use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\PrivateConstruct;
use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\PrivateConstructorClass;

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
