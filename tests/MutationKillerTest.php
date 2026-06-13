<?php

declare(strict_types=1);

namespace MF1DD\ObjectBuilder\Tests;

use MF1DD\ObjectBuilder\ClassBuilder\Dto\NoValueSet;
use MF1DD\ObjectBuilder\DataTypes\ArrayBuilder;
use MF1DD\ObjectBuilder\DataTypes\BooleanBuilder;
use MF1DD\ObjectBuilder\DataTypes\CallbackBuilder;
use MF1DD\ObjectBuilder\DataTypes\FloatBuilder;
use MF1DD\ObjectBuilder\DataTypes\IntegerBuilder;
use MF1DD\ObjectBuilder\DataTypes\NullBuilder;
use MF1DD\ObjectBuilder\DataTypes\SimpleObjectBuilder;
use MF1DD\ObjectBuilder\DataTypes\StringBuilder;
use MF1DD\ObjectBuilder\Dto\Constraints;
use MF1DD\ObjectBuilder\Dto\Property;
use MF1DD\ObjectBuilder\ObjectBuilder;
use MF1DD\ObjectBuilder\Services\DataTypeService;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\Address;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Entity\ReadonlyPerson;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\MyTestEnumeration;
use MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Trait\MyTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MutationKillerTest extends TestCase
{
    public function testNoValueSetToString(): void
    {
        $nvs = new NoValueSet();
        $this->assertSame('null', (string)$nvs);
    }

    public function testArrayBuilderDefaultValue(): void
    {
        $b = new ArrayBuilder();
        $this->assertSame(['a' => 13], $b->build());
    }

    public function testArrayBuilderWithGivenValue(): void
    {
        $b = new ArrayBuilder();
        $b->setProperty(new Property(name: 'x', type: 'array', value: ['foo' => 'bar']));
        $this->assertSame(['foo' => 'bar'], $b->build());
    }

    public function testArrayBuilderBuildAsString(): void
    {
        $b = new ArrayBuilder();
        $this->assertIsString($b->buildAsString());
        $this->assertStringContainsString('13', $b->buildAsString());
    }

    public function testBooleanBuilderReturnsBool(): void
    {
        $b = new BooleanBuilder();
        $result = $b->build();
        $this->assertIsBool($result);
    }

    public function testBooleanBuilderWithGivenValue(): void
    {
        $b = new BooleanBuilder();
        $b->setProperty(new Property(name: 'x', type: 'bool', value: true));
        $this->assertTrue($b->build());
    }

    public function testBooleanBuilderBuildAsStringMatchesBuild(): void
    {
        $b = new BooleanBuilder();
        $b->setProperty(new Property(name: 'x', type: 'bool', value: true));
        $this->assertSame('true', $b->buildAsString());

        $b2 = new BooleanBuilder();
        $b2->setProperty(new Property(name: 'x', type: 'bool', value: false));
        $this->assertSame('false', $b2->buildAsString());
    }

    public function testNullBuilderBuildReturnsNull(): void
    {
        $b = new NullBuilder();
        $this->assertNull($b->build());
    }

    public function testNullBuilderBuildAsString(): void
    {
        $b = new NullBuilder();
        $this->assertSame('null', $b->buildAsString());
    }

    public function testNullBuilderSetPropertyDoesNotThrow(): void
    {
        $b = new NullBuilder();
        $p = new Property(name: 'x', type: 'null', value: null);
        $ret = $b->setProperty($p);
        $this->assertInstanceOf(NullBuilder::class, $ret);
    }

    public function testSimpleObjectBuilderReturnsObject(): void
    {
        $b = new SimpleObjectBuilder();
        $obj = $b->build();
        $this->assertIsObject($obj);
        $this->assertSame([], (array)$obj);
    }

    public function testCallbackBuilderReturnsCallable(): void
    {
        $b = new CallbackBuilder();
        $fn = $b->build();
        $this->assertIsCallable($fn);
        $this->assertSame(5, $fn(2, 3));
    }

    public function testCallbackBuilderBuildAsString(): void
    {
        $b = new CallbackBuilder();
        $this->assertSame('function () { return 42; }', $b->buildAsString());
    }

    public function testFloatBuilderSetPropertyAcceptsNull(): void
    {
        $b = new FloatBuilder();
        $b->setProperty(new Property(name: 'x', type: 'float', value: new NoValueSet()));
        $result = $b->build();
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0.0, $result);
    }

    public function testIntegerBuilderSetPropertyAcceptsNull(): void
    {
        $b = new IntegerBuilder();
        $b->setProperty(new Property(name: 'x', type: 'int', value: new NoValueSet()));
        $result = $b->build();
        $this->assertIsInt($result);
    }

    public function testStringBuilderGivenValue(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'x', type: 'string', value: 'hello'));
        $this->assertSame('hello', $b->build());
    }

    public function testStringBuilderBuildAsStringQuotesValue(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'x', type: 'string', value: 'test'));
        $this->assertSame("'test'", $b->buildAsString());
    }

    public function testStringBuilderTimezone(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'timezone', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertContains($result, timezone_identifiers_list());
    }

    public function testStringBuilderDateTimeFormat(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'datetime', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testStringBuilderCountryCode(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'countrycode', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertSame(2, strlen($result));
        $this->assertSame(strtoupper($result), $result);
    }

    public function testStringBuilderEmailPattern(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'email', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^[a-z0-9]+@[a-z]+\.[a-z]+$/', $result);
    }

    public function testStringBuilderPhonePattern(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'phone', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertStringStartsWith('+', $result);
        $this->assertStringContainsString(' ', $result);
    }

    public function testStringBuilderFirstname(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'firstname', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[A-Z]/', $result);
    }

    public function testStringBuilderLastname(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'lastname', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[A-Z]/', $result);
    }

    public function testStringBuilderCity(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'city', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertNotEmpty($result);
    }

    public function testStringBuilderStreet(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'street', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertStringContainsString(' ', $result);
    }

    public function testStringBuilderZip(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(name: 'zip', type: 'string', value: new NoValueSet()));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^\d{5}$/', $result);
    }

    public function testStringBuilderFormatEmail(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'email'])
        ));
        $result = $b->build();
        $this->assertStringContainsString('@', $result);
    }

    public function testStringBuilderFormatUrl(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'url'])
        ));
        $result = $b->build();
        $this->assertStringStartsWith('https://', $result);
    }

    public function testStringBuilderFormatUuid(): void
    {
        $b = new StringBuilder();
        $b->setProperty(new Property(
            name: 'any', type: 'string', value: new NoValueSet(),
            constraints: new Constraints(['format' => 'uuid'])
        ));
        $result = $b->build();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $result);
        $this->assertSame(36, strlen($result));
    }

    public function testDataTypeServiceGetNullType(): void
    {
        $this->assertNull(DataTypeService::getDataTypeFromString(null));
    }

    public function testDataTypeServiceGetNullableType(): void
    {
        $result = DataTypeService::getDataTypeFromString('?int');
        $this->assertSame(['?', 'int'], $result);
    }

    public function testDataTypeServiceGetUnionType(): void
    {
        $result = DataTypeService::getDataTypeFromString('int|string');
        $this->assertSame(['int', 'string'], $result);
    }

    public function testDataTypeServiceGetIntersectionType(): void
    {
        $result = DataTypeService::getDataTypeFromString('Countable&Iterator');
        $this->assertSame(['Countable', 'Iterator'], $result);
    }

    public function testDataTypeServiceGetSimpleType(): void
    {
        $result = DataTypeService::getDataTypeFromString('int');
        $this->assertSame(['int'], $result);
    }

    public function testDataTypeServiceCustomBuilderPersistence(): void
    {
        $custom = new NullBuilder();
        DataTypeService::register('xx-null', $custom);
        $this->assertSame($custom, DataTypeService::getDataTypeBuilder('xx-null'));
        DataTypeService::reset();
        $this->assertNull(DataTypeService::getDataTypeBuilder('xx-null'));
    }

    public function testConstraintsMinMaxLength(): void
    {
        $c = new Constraints(['min' => 0, 'max' => 0, 'min_length' => 1, 'max_length' => 1]);
        $this->assertSame(0, $c->min());
        $this->assertSame(0, $c->max());
        $this->assertSame(1, $c->minLength());
        $this->assertSame(1, $c->maxLength());
        $this->assertNull($c->format());
        $this->assertNull($c->length());
    }

    public function testIntegerBuilderWithFloatConstraintCast(): void
    {
        $b = new IntegerBuilder();
        $b->setProperty(new Property(
            name: 'x', type: 'int', value: new NoValueSet(),
            constraints: new Constraints(['min' => '5', 'max' => '10'])
        ));
        $result = $b->build();
        $this->assertGreaterThanOrEqual(5, $result);
        $this->assertLessThanOrEqual(10, $result);
    }

    public function testFloatBuilderWithConstraintsBothEnds(): void
    {
        $b = new FloatBuilder();
        $b->setProperty(new Property(
            name: 'x', type: 'float', value: new NoValueSet(),
            constraints: new Constraints(['min' => 10, 'max' => 10])
        ));
        $this->assertSame(10.0, $b->build());
    }

    public function testE2EAddressWithAllProps(): void
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

    public function testE2EAddressWithStringZip(): void
    {
        $addr = ObjectBuilder::init(Address::class, [
            'zip' => 'ABC123',
        ])->build();
        $this->assertSame('ABC123', $addr->getZip());
    }

    public function testE2EEnumExactValue(): void
    {
        $e = ObjectBuilder::init(MyTestEnumeration::class, ['OK'])->build();
        $this->assertSame(MyTestEnumeration::OK, $e);
    }

    public function testE2ETraitPropertyAccess(): void
    {
        $trait = ObjectBuilder::init(MyTrait::class)->build();
        $this->assertSame('MyTrait', $trait->trait);
        $this->assertIsObject($trait);
    }

    public function testE2EReadonlyPersonAllPropsSet(): void
    {
        $p = ObjectBuilder::init(ReadonlyPerson::class, [
            'name' => 'Foo', 'age' => 99, 'address' => null,
        ])->build();
        $this->assertSame('Foo', $p->name);
        $this->assertSame(99, $p->age);
        $this->assertNull($p->address);
    }

    public function testE2EWithConstraintAllEqual(): void
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
        $result = ObjectBuilder::init(\MF1DD\ObjectBuilder\Tests\ClassBuilder\Helper\Interface\SimpleTestInterface::class)->build();
        $this->assertNull($result::post());
    }

    public function testFileContentHandlerGetReturnType(): void
    {
        $handler = new \MF1DD\ObjectBuilder\ClassBuilder\Interface\FileContentHandler();
        $type = $handler->getReturnType('int|string');
        $this->assertContains($type, ['int', 'string']);
    }
}
