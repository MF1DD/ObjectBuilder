<?php
declare(strict_types=1);

namespace MF1DD\Tests\Integration;

use DateInterval;
use DatePeriod;
use ReflectionClass;
use ReflectionException;
use MF1DD\UserInterface\ObjectBuilder;
use PHPUnit\Framework\TestCase;
use MF1DD\Tests\Helper\Address;
use MF1DD\Tests\Helper\Name;
use MF1DD\Tests\Helper\Person;
use MF1DD\Tests\Helper\ReadonlyPerson;
use MF1DD\Tests\Helper\ReadonlyAddress;
use MF1DD\Tests\Helper\AbstractVehicle;
use MF1DD\Tests\Helper\MyInterface;
use MF1DD\Tests\Helper\StockClass;
use MF1DD\Tests\Helper\MyTrait;
use MF1DD\Tests\Helper\MyTestEnumeration;
use MF1DD\Tests\Helper\SimpleTestInterface;
use MF1DD\Tests\Helper\SimpleReturnValueTestInterface;
use MF1DD\Tests\Helper\SimpleReturnObjectTestInterface;

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
        class_exists(\MF1DD\Tests\Helper\Car::class);

        $vehicle = ObjectBuilder::init(AbstractVehicle::class)->build();
        $this->assertInstanceOf(AbstractVehicle::class, $vehicle);
        $this->assertIsString($vehicle->brand);
    }

    public function testAddressWithAllProps(): void
    {
        $addr = ObjectBuilder::init(Address::class, [
            'street' => 'Test St',
            'zip' => 12345,
            'city' => 'Test City',
            'country' => 'DE',
            'mainResidence' => true,
        ])->build();
        $this->assertSame('Test St', $addr->getStreet());
        $this->assertSame(12345, $addr->getZip());
        $this->assertSame('Test City', $addr->getCity());
        $this->assertSame('DE', $addr->getCountry());
        $this->assertTrue($addr->isMainResidence());
    }

    public function testAddressWithStringZip(): void
    {
        $addr = ObjectBuilder::init(Address::class, [
            'zip' => 'ABC123',
        ])->build();
        $this->assertSame('ABC123', $addr->getZip());
    }

    public function testEnumExactValue(): void
    {
        $e = ObjectBuilder::init(MyTestEnumeration::class, ['OK'])->build();
        $this->assertSame(MyTestEnumeration::OK, $e);
    }

    public function testTraitPropertyAccess(): void
    {
        $trait = ObjectBuilder::init(MyTrait::class)->build();
        $this->assertSame('MyTrait', $trait->trait);
        $this->assertIsObject($trait);
    }

    public function testReadonlyPersonAllPropsSet(): void
    {
        $p = ObjectBuilder::init(ReadonlyPerson::class, [
            'name' => 'Foo', 'age' => 99, 'address' => null,
        ])->build();
        $this->assertSame('Foo', $p->name);
        $this->assertSame(99, $p->age);
        $this->assertNull($p->address);
    }

    public function testWithConstraintAllEqual(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $p = ObjectBuilder::init(ReadonlyPerson::class)
                ->with('age', ['min' => 42, 'max' => 42])
                ->build();
            $this->assertSame(42, $p->age);
        }
    }

    public function testInterfaceBuilderWithStaticMethod(): void
    {
        $result = ObjectBuilder::init(SimpleTestInterface::class)->build();
        $this->assertNull($result::post());
    }

    public function testInterfaceWithAllParamTypes(): void
    {
        $result = ObjectBuilder::init(SimpleReturnValueTestInterface::class, [
            'getString' => 'test',
            'getInt' => 42,
            'getFloat' => 3.14,
            'getBool' => true,
        ])->build();

        $this->assertSame('test', $result->getString());
        $this->assertSame(42, $result->getInt());
        $this->assertSame(3.14, $result->getFloat());
        $this->assertTrue($result->getBool());
    }

    public function testInterfaceWithObjectReturn(): void
    {
        $result = ObjectBuilder::init(
            SimpleReturnObjectTestInterface::class,
            ['getAddress' => ['street' => 'Interface St', 'city' => 'Interface City']]
        )->build();

        $this->assertInstanceOf(Address::class, $result->getAddress());
        $this->assertSame('Interface St', $result->getAddress()->getStreet());
        $this->assertSame('Interface City', $result->getAddress()->getCity());
    }

    public function testValueConstraintStringFormatUuid(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class)
            ->with('name', ['format' => 'uuid'])
            ->build();
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
            $person->name
        );
    }

    public function testNameClassWithoutConstructor(): void
    {
        $name = ObjectBuilder::init(Name::class)->build();
        $this->assertInstanceOf(Name::class, $name);
        $this->assertIsString($name->getFirstName());
        $this->assertIsString($name->getLastName());
    }

    public function testAddressRandom(): void
    {
        $addr = ObjectBuilder::init(Address::class)->build();
        $this->assertInstanceOf(Address::class, $addr);
        $this->assertIsString($addr->getCity());
        $result = $addr->getZip();
        $this->assertTrue(is_int($result) || is_string($result));
        $this->assertIsBool($addr->isMainResidence());
    }
}
