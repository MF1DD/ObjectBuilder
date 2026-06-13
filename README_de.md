# ObjectBuilder

Automatische Objekt-Erstellung mit Zufallswerten für PHP 8.2+.

## Wofür?

Manuelles Erstellen von Test-Objekten ist repetitiv, fehleranfällig und
zeitraubend — besonders bei tief verschachtelten Objektgraphen mit
Dutzenden Properties. Jede Änderung am Constructor zwingt zum
Nachziehen aller Test-Fixtures.

**ObjectBuilder** löst das: Klassenname rein, vollständig befüllte
Instanz raus. Rekursiv, typsicher, mit semantisch sinnvollen Werten.

## Vorteile

- **Kein Boilerplate** — `ObjectBuilder::init(Foo::class)->build()`
  statt händischem `new Foo(...)` mit 10 Parametern
- **Typsicher** — Reflection-basiert, alle nativen und benutzerdefinierten
  Typen inkl. Enums, Interfaces, Traits, abstrakte und readonly-Klassen
- **Tiefe Objektgraphen** — verschachtelte Abhängigkeiten werden
  automatisch rekursiv aufgelöst (`Person → Address → Street`)
- **Semantische Werte** — erkennt Property-Namen (`email`, `timezone`,
  `firstname`) und liefert passende Zufallswerte
- **Constraints** — Wertebereiche und Formate via `with()`-API:
  `->with('age', ['min' => 18, 'max' => 65])`
- **Überschreibbar** — einzelne Properties gezielt mit festen Werten
  belegen, der Rest bleibt zufällig
- **Erweiterbar** — eigene Typ-Builder und Stock-Class-Handler
  registrierbar, Builder austauschbar
- **Kein Framework** — reine PHP-Library, null externe Runtime-Dependencies
  außer `nikic/php-parser`

## Kompatibilität

| PHP-Version | Status |
|---|---|
| 8.2 | Voll unterstützt, CI-getestet |
| 8.3 | Voll unterstützt, CI-getestet |
| 8.4 | Voll unterstützt, CI-getestet |
| 8.5 | CI-getestet (sobald verfügbar) |

- **Runtime-Dependency**: `nikic/php-parser ^5.0`
- **Kein Framework** (Symfony, Laravel, etc.) nötig
- **Package-Name**: `mf1dd/object-builder`

## Basic Usage
Einfache Klassen werden automatisch mit zufälligen Werten befüllt.
```php
class Address
{
    public function __construct(
        private readonly mixed $street,
        private readonly string|int $zip,
        private readonly string $city,
        private readonly ?string $country,
        private readonly bool $mainResidence,
    ) {}
}

$result = ObjectBuilder::init(Address::class)->build();
// returns instance of Address with random values
```

Du kannst bestimmte Werte überschreiben. Nicht gesetzte Werte werden zufällig generiert.
Dabei werden auch verschachtelte Objekte automatisch aufgelöst.
```php
class Person
{
    public function __construct(
        private readonly Name $name,
        private readonly int $age,
        private readonly Address $address,
    ) {}
}

$result = ObjectBuilder::init(Person::class, [
    'age' => 25,
    'name' => [
        'firstName' => 'Max',
        'lastName' => 'Mustermann'
    ],
    'address' => [
        'city' => 'Berlin',
    ]
])->build();
// $result->getAge() === 25
// $result->getName()->getFirstName() === 'Max'
// $result->getAddress()->getCity() === 'Berlin'
// $result->getAddress()->getZip() === random int|string
```

## Enumeration
```php
enum MyEnumeration: string
{
    case OK = 'OK';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
}
```
```php
$result = ObjectBuilder::init(MyEnumeration::class)->build();
// returns one of MyEnumeration cases
```
Du kannst bei einem Enum den Wert bestimmen der Verwendet werden soll.
```php
$result = ObjectBuilder::init(MyEnumeration::class, ['OK'])->build();
// returns MyEnumeration::OK

$result = ObjectBuilder::init(MyEnumeration::class, ['WARNING', 'ERROR'])->build();
// returns one of MyEnumeration::WARNING|MyEnumeration::ERROR
```

## Trait
Für übergebene Traits wird eine anonyme Klasse erzeugt die den Trait verwendet.
Übergebene Parameter werden vom TraitBuilder nicht berücksichtigt.
```php
$result = ObjectBuilder::init(MyTrait::class)->build();
// returns {class@anonymous/...}
```

## Interface
Das übergebene Interface wird geladen und daraus dynamisch eine Klasse erzeugt.
Diese liefert das Interface mit den benötigten Methoden zurück und implementiert das Interface.

Der Rückgabewert der Methoden wird ermittelt und den Methoden ein random Wert zugeteilt.
```php
$result = ObjectBuilder::init(MyInterface::class)->build();
// returns Object of MyInterfaceClass
$value = $result->myMethode()
// returns random value of his return type.
```
Du kannst bestimmen welche Werte die Methoden zurückliefern.
Dazu übergibst du ein Array mit dem Methodennamen als key.
```php
$options = [
    'getMyString' => 'testString'
];

$result = ObjectBuilder::init(MyInterface::class, $options)->build();
// returns Object of MyInterfaceClass
$value = $result->getMyString()
// returns 'testString'
```
Gibt die Methode ein Object zurück in dem du werte setzen möchtest, geht das auch.
```php
$options = [
    'getMyObject' => new SomeObject('Gustav', 27)
];

$result = ObjectBuilder::init(MyInterface::class, $options)->build();
// returns Object of MyInterfaceClass
$value = $result->getMyObject()
/**
 * returns class SomeObject {
 *      string $name => "Gustav",
 *      int $age => 27,
 * }
 */
```
Es ist auch möglich die Parameter einzeln an das Object weiterzureichen.
```php
$options = [
    'getMyObject' => ['name' => 'Bernhard']
];

$result = ObjectBuilder::init(MyInterface::class, $options)->build();
// returns Object of MyInterfaceClass
$value = $result->getMyObject()
/**
 * returns class SomeObject {
 *      string $name => "Bernhard",
 *      int $age => 27356453,
 * }
 */
```

## Readonly Classes
Readonly-Klassen (PHP 8.2+) werden unterstützt. Properties werden automatisch
im Konstruktor befüllt — auch verschachtelt.
```php
readonly class ReadonlyPerson
{
    public function __construct(
        public string $name,
        public int $age,
        public ?ReadonlyAddress $address = null,
    ) {}
}

$result = ObjectBuilder::init(ReadonlyPerson::class, [
    'name' => 'Alice',
    'address' => ['street' => 'Main St', 'city' => 'Springfield'],
])->build();
// $result->name === 'Alice'
// $result->address->street === 'Main St'
```

## Abstract Classes
Abstrakte Klassen werden über existierende konkrete Subklassen aufgelöst.
Der Builder sucht automatisch eine passende Implementierung.
```php
abstract class AbstractVehicle
{
    public function __construct(public readonly string $brand) {}
    abstract public function getType(): string;
}

class Car extends AbstractVehicle
{
    public function getType(): string { return 'car'; }
}

$result = ObjectBuilder::init(AbstractVehicle::class)->build();
// returns instance of Car (or another concrete subclass)
```

## Stock Classes (PHP Built-Ins)
Built-in PHP-Klassen wie DateInterval, DatePeriod, DateTime, DateTimeImmutable,
ReflectionFunction, ArrayObject und SplFileInfo werden automatisch unterstützt.
```php
$interval = ObjectBuilder::init(DateInterval::class)->build();
// returns DateInterval('P7D')

$date = ObjectBuilder::init(DateTimeImmutable::class)->build();
// returns random DateTimeImmutable instance

$ref = ObjectBuilder::init(ReflectionFunction::class)->build();
// returns new ReflectionFunction('strlen')
```

Eigene Handler für weitere Stock-Klassen können registriert werden:
```php
use MF1DD\ObjectBuilder\Services\StockClassHandlerService;

StockClassHandlerService::register(new MyCustomHandler());
```

## Value Constraints (`with()`)
Mit der `with()`-Methode können Constraints für Wertebereiche gesetzt werden.
```php
use MF1DD\ObjectBuilder\ObjectBuilder;

$result = ObjectBuilder::init(Person::class)
    ->with('age', ['min' => 18, 'max' => 65])
    ->with('email', ['format' => 'email'])
    ->build();
// $result->getAge() ist zwischen 18 und 65
// $result->getEmail() ist eine zufällige E-Mail-Adresse
```

Verfügbare Constraints:
- `min` / `max` — Wertebereich für int und float
- `min_length` / `max_length` — String-Länge
- `format` — Vordefinierte Formate: `email`, `url`, `uuid`

## Semantic String Detection
Der StringBuilder erkennt bestimmte Property-Namen und liefert passende Werte:
```php
class User
{
    public function __construct(
        public readonly string $timezone,    // random IANA timezone
        public readonly string $countrycode, // random ISO country code
        public readonly string $email,       // random email address
        public readonly string $firstname,   // random first name
        public readonly string $lastname,    // random last name
        public readonly string $city,        // random city name
        public readonly string $street,      // random street name
        public readonly string $zip,         // random postal code
        public readonly string $phone,       // random phone number
        public readonly string $uuid,        // random UUID v4
        public readonly string $url,         // random URL
    ) {}
}

$result = ObjectBuilder::init(User::class)->build();
// All properties contain semantically meaningful random values
```

## Custom Type Builders
Eigene Typ-Builder für spezielle Datentypen können registriert werden:
```php
use MF1DD\ObjectBuilder\Services\DataTypeService;
use MF1DD\ObjectBuilder\DataTypes\DataTypeInterface;
use MF1DD\ObjectBuilder\Dto\Property;

class CustomBuilder implements DataTypeInterface
{
    public function build(): mixed
    {
        return 'custom value';
    }

    public function setProperty(Property $property): self
    {
        return $this;
    }

    public function buildAsString(): string
    {
        return "'custom value'";
    }
}

DataTypeService::register('custom_type', new CustomBuilder());
```

## Custom Builder Override
Der automatisch gewählte Builder kann überschrieben werden:
```php
use MF1DD\ObjectBuilder\ClassBuilder\ClassBuilderInterface;

$result = ObjectBuilder::init(MyClass::class)
    ->withBuilder($myCustomBuilder)
    ->build();
```
