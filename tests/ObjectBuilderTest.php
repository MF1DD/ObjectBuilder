<?php
declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests;

use DateInterval;
use DatePeriod;
use ReflectionClass;
use ReflectionException;
use MF1DD\UserInterface\ObjectBuilder;
use PHPUnit\Framework\TestCase;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Name;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Person;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\ReadonlyPerson;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\ReadonlyAddress;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\AbstractVehicle;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Interface\MyInterface;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\StockClass;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Trait\MyTrait;

class ObjectBuilderTest extends TestCase
{
    public function testTest2(): void
    {
        /** @var Person $person */
        $person = ObjectBuilder::init(
            Person::class,
            [
                'status' => ['WARNING']
            ]
        )->build();

//        var_dump($person);
        $this->assertInstanceOf(Person::class, $person);

    }
    public function testEnum(): void
    {
        /** @var Person $person */
        $person = ObjectBuilder::init(
            Person::class,
            [
                'age' => 25,
                'name' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ],
                'address' => [
                    'city' => 'Berlin',
                    'country' => null
                ]
            ]
        )->build();


        $this->assertInstanceOf(Person::class, $person);
        $this->assertInstanceOf(Name::class, $person->getName());
        $this->assertInstanceOf(Address::class, $person->getAddress());
        $this->assertEquals('Max', $person->getName()->getFirstName());
        $this->assertEquals('Berlin', $person->getAddress()->getCity());
        $this->assertEquals(null, $person->getAddress()->getCountry());
        $this->assertEquals('Mustermann', $person->getName()->getLastName());
        $this->assertEquals(25, $person->getAge());

    }

    /**
     * @throws ReflectionException
     */
    public function testTrait(): void
    {
        /** @var MyTrait $trait */
        $trait = ObjectBuilder::init(MyTrait::class)->build();
        $class = new ReflectionClass(
            $trait
        );

        $this->assertArrayHasKey(
            MyTrait::class,
            $class->getTraits()
        );

        $array = $trait->toArray('[1,2,3]');
        $this->assertEquals([1,2,3], $array);
        $this->assertSame('Hello', $trait->sayHello());
        $this->assertSame('MyTrait', $trait->trait);
    }

    public function testStockClasses(): void
    {
        $stockClasses1 = ObjectBuilder::init(MyInterface::class, [
            'get' => ObjectBuilder::init(Address::class, [
                'street' => 'Leipziger Straße'
            ])->build(),
            'put' => 'Hannes',
            'post' => [
                'street' => 'Bremer Straße'
            ]
        ])->build();
        $stockClasses2 = ObjectBuilder::init(Address::class, [
            'street' => 'Leipziger Straße'
        ])->build();
        $stockClasses3 = ObjectBuilder::init(Person::class)->build();

//        var_dump($stockClasses1);
//        var_dump($stockClasses2);

        $this->assertTrue(true);
    }

    public function testTest(): void
    {
        $classes = get_declared_classes();

        $classesInGlobalNamespace = array_filter($classes, fn($class) => !str_contains($class, '\\'));

//        print_r($classesInGlobalNamespace);

        $this->assertTrue(true);

    }

    public function testStockClass(): void
    {
        $stockClass = ObjectBuilder::init(DateInterval::class)->build();
        $this->assertInstanceOf(DateInterval::class, $stockClass);
        $stockClass = ObjectBuilder::init(DatePeriod::class)->build();
        $this->assertInstanceOf(DatePeriod::class, $stockClass);
        $stockClass = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stockClass);
        $stockClass = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stockClass);
        $stockClass = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stockClass);
        $stockClass = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stockClass);
        $stockClass = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stockClass);
    }

    public function testNewStockClasses(): void
    {
        $this->assertInstanceOf(\ReflectionFunction::class, ObjectBuilder::init(\ReflectionFunction::class)->build());
        $this->assertInstanceOf(\ArrayObject::class, ObjectBuilder::init(\ArrayObject::class)->build());
        $this->assertInstanceOf(\SplFileInfo::class, ObjectBuilder::init(\SplFileInfo::class)->build());
        $this->assertInstanceOf(\DateTimeImmutable::class, ObjectBuilder::init(\DateTimeImmutable::class)->build());
        $this->assertInstanceOf(\DateTime::class, ObjectBuilder::init(\DateTime::class)->build());
    }

    public function testReadonlyClass(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class)->build();
        $this->assertInstanceOf(ReadonlyPerson::class, $person);
        $this->assertIsString($person->name);
        $this->assertIsInt($person->age);
    }

    public function testReadonlyClassWithNestedReadonlyObject(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class, [
            'name' => 'Alice',
            'age' => 30,
            'address' => [
                'street' => 'Main St',
                'city' => 'Springfield',
            ],
        ])->build();

        $this->assertInstanceOf(ReadonlyPerson::class, $person);
        $this->assertSame('Alice', $person->name);
        $this->assertSame(30, $person->age);
        $this->assertInstanceOf(ReadonlyAddress::class, $person->address);
        $this->assertSame('Main St', $person->address->street);
        $this->assertSame('Springfield', $person->address->city);
    }

    public function testAbstractClass(): void
    {
        class_exists(AbstractVehicle::class);
        class_exists(\MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Car::class);

        $vehicle = ObjectBuilder::init(AbstractVehicle::class)->build();
        $this->assertInstanceOf(AbstractVehicle::class, $vehicle);
        $this->assertIsString($vehicle->brand);
    }
}
