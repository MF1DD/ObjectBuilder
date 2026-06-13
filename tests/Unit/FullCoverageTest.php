<?php

declare(strict_types=1);

namespace MF1DD\Tests\Unit;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use MF1DD\Application\AbstractClassBuilder;
use MF1DD\Application\EnumBuilder;
use MF1DD\Application\Interface\FileContentHandler;
use MF1DD\Domain\HandlerInterface;
use MF1DD\Application\Interface\ImplementedClassHandler;
use MF1DD\Application\Interface\ThrowableHandler;
use MF1DD\Application\InterfaceBuilder;
use MF1DD\Application\Services\HandlerService;
use MF1DD\Application\TraitBuilder;
use MF1DD\Infrastructure\ArrayBuilder;
use MF1DD\Infrastructure\BooleanBuilder;
use MF1DD\Infrastructure\CallbackBuilder;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Infrastructure\FloatBuilder;
use MF1DD\Infrastructure\IntegerBuilder;
use MF1DD\Infrastructure\NullBuilder;
use MF1DD\Infrastructure\SimpleObjectBuilder;
use MF1DD\Infrastructure\StringBuilder;
use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\Property;
use MF1DD\UserInterface\ObjectBuilder;
use MF1DD\Application\Services\DataTypeService;
use MF1DD\Tests\Fixture\AbstractVehicle;
use MF1DD\Tests\Fixture\Address;
use MF1DD\Tests\Fixture\Car;
use MF1DD\Tests\Fixture\Name;
use MF1DD\Tests\Fixture\Person;
use MF1DD\Tests\Fixture\PrivateConstruct;
use MF1DD\Tests\Fixture\ReadonlyAddress;
use MF1DD\Tests\Fixture\ReadonlyPerson;
use MF1DD\Tests\Fixture\MyTestEnumeration;
use MF1DD\Tests\Fixture\MyTestTrait;
use MF1DD\Tests\Fixture\StockClass;
use MF1DD\Tests\Fixture\MyTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Exceptions\ObjectBuilderWrongClassesGivenException;

class FullCoverageTest extends TestCase
{
    // ========== DTOs ==========

    public function testConstraintsAllGetters(): void
    {
        $c = new Constraints(['min' => 1, 'max' => 10, 'length' => 5, 'format' => 'email', 'min_length' => 3, 'max_length' => 20]);
        $this->assertSame(1, $c->min());
        $this->assertSame(10, $c->max());
        $this->assertSame(5, $c->length());
        $this->assertSame('email', $c->format());
        $this->assertSame(3, $c->minLength());
        $this->assertSame(20, $c->maxLength());
    }

    public function testConstraintsEmptyReturnsNull(): void
    {
        $c = new Constraints([]);
        $this->assertNull($c->min());
        $this->assertNull($c->max());
        $this->assertNull($c->length());
        $this->assertNull($c->format());
    }

    public function testPropertyWithConstraints(): void
    {
        $constraints = new Constraints(['min' => 5, 'max' => 100]);
        $p = new Property(name: 'test', type: 'int', value: 42, constraints: $constraints);
        $this->assertSame('test', $p->name);
        $this->assertSame('int', $p->type);
        $this->assertSame(42, $p->value);
        $this->assertSame(5, $p->constraints?->min());
    }

    // ========== DataType Builders ==========

    public function testArrayBuilderBuildAsString(): void
    {
        $builder = new ArrayBuilder();
        $this->assertIsArray($builder->build());
        $this->assertIsString($builder->buildAsString());
    }

    public function testBooleanBuilderBuildAsString(): void
    {
        $builder = new BooleanBuilder();
        $result = $builder->build();
        $this->assertIsBool($result);
        $str = $builder->buildAsString();
        $this->assertContains($str, ['true', 'false']);
    }

    public function testBooleanBuilderSetPropertyWithValue(): void
    {
        $builder = new BooleanBuilder();
        $builder->setProperty(new Property(name: 'test', type: 'bool', value: null));
        $this->assertTrue(true); // no exception = pass
    }

    public function testCallbackBuilder(): void
    {
        $builder = new CallbackBuilder();
        $this->assertIsCallable($builder->build());
        $this->assertSame('function () { return 42; }', $builder->buildAsString());
    }

    public function testNullBuilderBuildAsString(): void
    {
        $builder = new NullBuilder();
        $this->assertNull($builder->build());
        $this->assertSame('null', $builder->buildAsString());
    }

    public function testSimpleObjectBuilder(): void
    {
        $builder = new SimpleObjectBuilder();
        $obj = $builder->build();
        $this->assertIsObject($obj);
        $this->assertSame('(object)[]', $builder->buildAsString());
    }

    public function testFloatBuilderWithConstraints(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 1, 'max' => 10])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(1.0, $result);
        $this->assertLessThanOrEqual(10.0, $result);
    }

    public function testFloatBuilderMinOnly(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 5])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(5.0, $result);
    }

    public function testFloatBuilderMaxOnly(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(
            name: 'score', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['max' => 5])
        ));
        $result = $builder->build();
        $this->assertLessThanOrEqual(5.0, $result);
    }

    public function testFloatBuilderWithoutConstraints(): void
    {
        $builder = new FloatBuilder();
        $builder->setProperty(new Property(name: 'score', type: 'float', value: new NoValueSet()));
        $result = $builder->build();
        $this->assertIsFloat($result);
    }

    public function testIntegerBuilderWithMinMaxConstraints(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => 18, 'max' => 65])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(18, $result);
        $this->assertLessThanOrEqual(65, $result);
    }

    public function testIntegerBuilderMinOnly(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => 100])
        ));
        $result = $builder->build();
        $this->assertGreaterThanOrEqual(100, $result);
    }

    public function testIntegerBuilderMaxOnly(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(
            name: 'age', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['max' => 50])
        ));
        $result = $builder->build();
        $this->assertLessThanOrEqual(50, $result);
    }

    public function testIntegerBuilderWithoutConstraints(): void
    {
        $builder = new IntegerBuilder();
        $builder->setProperty(new Property(name: 'age', type: 'int', value: new NoValueSet()));
        $result = $builder->build();
        $this->assertIsInt($result);
    }

    public function testStringBuilderSemanticNames(): void
    {
        $names = ['timezone', 'countrycode', 'email', 'firstname', 'lastname', 'city', 'street', 'zip', 'phone', 'uuid', 'url', 'postcode'];
        foreach ($names as $name) {
            $builder = new StringBuilder();
            $builder->setProperty(new Property(name: $name, type: 'string', value: new NoValueSet()));
            $result = $builder->build();
            $this->assertIsString($result, "Failed for name: $name");
            $this->assertNotEmpty($result, "Empty result for name: $name");
        }
    }

    public function testStringBuilderFormatConstraints(): void
    {
        $builder = new StringBuilder();
        $builder->setProperty(new Property(
            name: 'email', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'email'])
        ));
        $result = $builder->build();
        $this->assertStringContainsString('@', $result);
    }

    public function testStringBuilderUrlFormat(): void
    {
        $builder = new StringBuilder();
        $builder->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'url'])
        ));
        $result = $builder->build();
        $this->assertStringStartsWith('https://', $result);
    }

    public function testStringBuilderUuidFormat(): void
    {
        $builder = new StringBuilder();
        $builder->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'uuid'])
        ));
        $result = $builder->build();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $result);
    }

    public function testStringBuilderWithLengthConstraints(): void
    {
        $builder = new StringBuilder();
        $builder->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['min_length' => 2, 'max_length' => 2])
        ));
        $result = $builder->build();
        $this->assertSame(2, strlen($result));
    }

    public function testStringBuilderWithGivenValue(): void
    {
        $builder = new StringBuilder();
        $builder->setProperty(new Property(name: 'test', type: 'string', value: 'hello'));
        $this->assertSame('hello', $builder->build());
        $this->assertSame("'hello'", $builder->buildAsString());
    }

    // ========== Stock Handlers ==========

    public function testArrayObjectHandlerSupport(): void
    {
        $handler = new \MF1DD\Infrastructure\StockClass\ArrayObjectHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(\ArrayObject::class)));
        $this->assertFalse($handler::supports(new ReflectionClass(\stdClass::class)));
    }

    public function testDateTimeImmutableHandlerSupport(): void
    {
        $handler = new \MF1DD\Infrastructure\StockClass\DateTimeImmutableHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(DateTimeImmutable::class)));
        $this->assertTrue($handler::supports(new ReflectionClass(DateTime::class)));
        $this->assertFalse($handler::supports(new ReflectionClass(\stdClass::class)));
    }

    public function testReflectionFunctionHandlerSupport(): void
    {
        $handler = new \MF1DD\Infrastructure\StockClass\ReflectionFunctionHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(\ReflectionFunction::class)));
    }

    public function testSplFileInfoHandlerSupport(): void
    {
        $handler = new \MF1DD\Infrastructure\StockClass\SplFileInfoHandler();
        $this->assertTrue($handler::supports(new ReflectionClass(\SplFileInfo::class)));
    }

    // ========== HandlerService ==========

    public function testHandlerServiceThrowable(): void
    {
        $handler = HandlerService::getHandler(new ReflectionClass(\Throwable::class));
        $this->assertInstanceOf(ThrowableHandler::class, $handler);
    }

    public function testHandlerServiceImplementedClass(): void
    {
        class_exists(Person::class);
        $ref = new ReflectionClass(\MF1DD\Tests\Fixture\SimpleTestInterface::class);
        $handler = HandlerService::getHandler($ref);
        $this->assertInstanceOf(FileContentHandler::class, $handler);
    }

    public function testFileContentHandlerSupport(): void
    {
        $ref = new ReflectionClass(\MF1DD\Tests\Fixture\EmptyTestInterface::class);
        $this->assertTrue(FileContentHandler::support($ref));
    }

    public function testFileContentHandlerSupportReturnsFalseForNonFileClass(): void
    {
        $ref = new ReflectionClass(\stdClass::class);
        $this->assertFalse(FileContentHandler::support($ref));
    }

    public function testThrowableHandlerExecute(): void
    {
        $handler = new ThrowableHandler();
        $result = $handler->execute(new ReflectionClass(\Throwable::class), []);
        $this->assertInstanceOf(\Throwable::class, $result);
    }

    // ========== TraitBuilder ==========

    public function testTraitBuilderDirect(): void
    {
        $builder = new TraitBuilder();
        $result = $builder->build(new ReflectionClass(MyTrait::class), []);
        $this->assertIsObject($result);
        $this->assertContains(MyTrait::class, (new ReflectionClass($result))->getTraitNames());
    }

    // ========== AbstractClassBuilder ==========

    public function testAbstractClassBuilderNoImplementation(): void
    {
        class_exists(AbstractVehicle::class);
        class_exists(Car::class);

        $builder = new AbstractClassBuilder();
        $result = $builder->build(new ReflectionClass(AbstractVehicle::class), []);
        $this->assertInstanceOf(AbstractVehicle::class, $result);
    }

    // ========== DataTypeService ==========

    public function testDataTypeServiceCustomBuilder(): void
    {
        $custom = new class implements DataTypeInterface {
            public function build(): mixed { return 'custom'; }
            public function setProperty(Property $property): self { return $this; }
            public function buildAsString(): string { return "'custom'"; }
        };
        DataTypeService::register('mytype', $custom);
        $builder = DataTypeService::getDataTypeBuilder('mytype');
        $this->assertSame('custom', $builder->build());
        DataTypeService::reset();
    }

    public function testDataTypeServiceGetTypeFromStringIntersection(): void
    {
        $result = DataTypeService::getDataTypeFromString('Countable&ArrayAccess');
        $this->assertSame(['Countable', 'ArrayAccess'], $result);
    }

    // ========== E2E Tests ==========

    public function testE2EPersonFullCustomization(): void
    {
        $person = ObjectBuilder::init(Person::class, [
            'name' => ['firstName' => 'Max', 'lastName' => 'Mustermann'],
            'age' => 42,
            'address' => ['city' => 'Berlin', 'country' => null],
            'status' => ['OK'],
        ])->build();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertInstanceOf(Name::class, $person->getName());
        $this->assertSame('Max', $person->getName()->getFirstName());
        $this->assertSame('Mustermann', $person->getName()->getLastName());
        $this->assertSame(42, $person->getAge());
        $this->assertInstanceOf(Address::class, $person->getAddress());
        $this->assertSame('Berlin', $person->getAddress()->getCity());
        $this->assertNull($person->getAddress()->getCountry());
    }

    public function testE2EReadonlyWithConstraints(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class)
            ->with('age', ['min' => 30, 'max' => 30])
            ->build();

        $this->assertInstanceOf(ReadonlyPerson::class, $person);
        $this->assertSame(30, $person->age);
    }

    public function testE2EReadonlyNestedDeep(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class, [
            'name' => 'Alice',
            'age' => 30,
            'address' => ['street' => 'Deep St', 'city' => 'Deep City'],
        ])->build();

        $this->assertInstanceOf(ReadonlyAddress::class, $person->address);
        $this->assertSame('Deep St', $person->address->street);
        $this->assertSame('Deep City', $person->address->city);
        $this->assertSame('12345', $person->address->zip);
    }

    public function testE2EInterfaceWithAllParamTypes(): void
    {
        $result = ObjectBuilder::init(\MF1DD\Tests\Fixture\SimpleReturnValueTestInterface::class, [
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

    public function testE2EInterfaceWithObjectReturn(): void
    {
        $result = ObjectBuilder::init(
            \MF1DD\Tests\Fixture\SimpleReturnObjectTestInterface::class,
            ['getAddress' => ['street' => 'Interface St', 'city' => 'Interface City']]
        )->build();

        $this->assertInstanceOf(Address::class, $result->getAddress());
        $this->assertSame('Interface St', $result->getAddress()->getStreet());
        $this->assertSame('Interface City', $result->getAddress()->getCity());
    }

    public function testE2EAllStockClasses(): void
    {
        $this->assertInstanceOf(DateInterval::class, ObjectBuilder::init(DateInterval::class)->build());
        $this->assertInstanceOf(DatePeriod::class, ObjectBuilder::init(DatePeriod::class)->build());
        $this->assertInstanceOf(DateTimeImmutable::class, ObjectBuilder::init(DateTimeImmutable::class)->build());
        $this->assertInstanceOf(DateTime::class, ObjectBuilder::init(DateTime::class)->build());
        $this->assertInstanceOf(\ArrayObject::class, ObjectBuilder::init(\ArrayObject::class)->build());
        $this->assertInstanceOf(\SplFileInfo::class, ObjectBuilder::init(\SplFileInfo::class)->build());
        $this->assertInstanceOf(\ReflectionFunction::class, ObjectBuilder::init(\ReflectionFunction::class)->build());
    }

    public function testE2EAbstractClassAutoResolve(): void
    {
        class_exists(Car::class);
        $vehicle = ObjectBuilder::init(AbstractVehicle::class)->build();
        $this->assertInstanceOf(AbstractVehicle::class, $vehicle);
        $this->assertIsString($vehicle->brand);
    }

    public function testE2EEnumAllVariants(): void
    {
        $e1 = ObjectBuilder::init(MyTestEnumeration::class)->build();
        $this->assertContains($e1, MyTestEnumeration::cases());

        $e2 = ObjectBuilder::init(MyTestEnumeration::class, ['OK'])->build();
        $this->assertSame(MyTestEnumeration::OK, $e2);
    }

    public function testE2ETraitWithMethods(): void
    {
        $trait = ObjectBuilder::init(MyTrait::class)->build();
        $this->assertSame('Hello', $trait->sayHello());
        $this->assertSame('MyTrait', $trait->trait);
        $array = $trait->toArray('[1,2,3]');
        $this->assertSame([1, 2, 3], $array);
    }

    public function testE2EWithBuilderOverride(): void
    {
        $enum = ObjectBuilder::init(MyTestEnumeration::class)
            ->withBuilder(new EnumBuilder())
            ->build();
        $this->assertContains($enum, MyTestEnumeration::cases());
    }

    public function testE2EValueConstraintOnAge(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $person = ObjectBuilder::init(Person::class)
                ->with('age', ['min' => 50, 'max' => 50])
                ->build();
            $this->assertSame(50, $person->getAge());
        }
    }

    public function testE2EValueConstraintStringFormatUuid(): void
    {
        $person = ObjectBuilder::init(ReadonlyPerson::class)
            ->with('name', ['format' => 'uuid'])
            ->build();
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
            $person->name
        );
    }

    public function testE2EPrivateConstructorStaticFactory(): void
    {
        $person = ObjectBuilder::init(PrivateConstruct::class)->build();
        $this->assertInstanceOf(PrivateConstruct::class, $person);
        $this->assertIsString($person->getName());
        $this->assertContains($person->getGender(), ['M', 'W', 'O']);
    }

    public function testE2EStockClassWithDatePeriod(): void
    {
        $stock = ObjectBuilder::init(StockClass::class)->build();
        $this->assertInstanceOf(StockClass::class, $stock);
        $this->assertInstanceOf(DatePeriod::class, $stock->DatePeriod);
    }

    public function testE2ENameClassWithoutConstructor(): void
    {
        $name = ObjectBuilder::init(Name::class)->build();
        $this->assertInstanceOf(Name::class, $name);
        $this->assertIsString($name->getFirstName());
        $this->assertIsString($name->getLastName());
    }

    public function testE2EAddressRandom(): void
    {
        $addr = ObjectBuilder::init(Address::class)->build();
        $this->assertInstanceOf(Address::class, $addr);
        $this->assertIsString($addr->getCity());
        $result = $addr->getZip();
        $this->assertTrue(is_int($result) || is_string($result));
        $this->assertIsBool($addr->isMainResidence());
    }
}
