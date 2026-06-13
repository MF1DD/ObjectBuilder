# ObjectBuilder - Vollständige Projektanalyse

## 1. Übersicht: Wofür ist das Projekt da?

**ObjectBuilder** (Packagist: `mf1dd/object-builder`) ist eine PHP-Bibliothek, die
automatisch Objekte mit zufälligen Werten erzeugt. Das primäre Anwendungsszenario sind
Test-Fixtures: Statt händisch Testdaten für Domain-Objekte zu konstruieren, erzeugt der
ObjectBuilder vollständig befüllte Instanzen beliebiger Klassen, Enums, Traits und
Interfaces. Das Werkzeug adressiert das klassische Problem des "Arrange"-Schritts in
Unit- und Integrationstests: die mühsame Erstellung komplexer Objektgraphen für
Test-Setups.

Die Kern-API ist einfach und fluent:

```php
ObjectBuilder::init(MyClass::class, ['param' => 'value'])->build();
```

Die Philosophie: *"Gib mir den Klassennamen und ich liefere dir eine Instanz mit
realistischen Zufallswerten. Überschreibe bestimmte Felder, wenn du konkrete Werte
brauchst."*

## 2. Welche Probleme soll es lösen?

| Problem | Lösungsansatz |
|---|---|
| **Manuelles Erstellen von Test-Objekten** ist repetitiv und fehleranfällig | Automatische Instanziierung per FQCN mit `build()` |
| **Komplexe Objektgraphen** (z.B. `Person->Address->Name`) manuell befüllen | Rekursives Auflösen aller Konstruktor-Parameter per Reflection |
| **Enum-Werte** in Tests zufällig oder bestimmt auswählen | `EnumBuilder` mit optionaler Filterung (`['OK', 'WARNING']`) |
| **Mocking von Interfaces** für Tests ist boilerplate-intensiv | Dynamische Interface-Implementierung aus Source-Code erzeugt |
| **Traits** können nicht direkt instanziiert werden | Anonyme Klasse via `eval()` die den Trait nutzt |
| **Klassen mit privatem Konstruktor** (Value Objects, Singletons) | Automatische Erkennung statischer Factory-Methoden |
| **Stock-Klassen der PHP-Standardbibliothek** (`DateInterval`, `DatePeriod`) haben spezielle Anforderungen | `tryExceptionSolver` parst Fehlermeldungen und probiert gültige Argument-Kombinationen |
| **Semantisch sinnvolle Strings** (z.B. für `$timezone`, `$countrycode`) | `StringBuilder` erkennt Feldnamen und liefert passende Werte |

## 3. Architektur und aktueller Ansatz

### 3.1 Einstiegspunkt und Ablauf

```
ObjectBuilder::init($className, $params)
    ↓
ReflectionClass erstellen
    ↓
ClassBuilderService::getClassBuilder($reflection)
    → Enum? → EnumBuilder
    → Trait? → TraitBuilder
    → Interface? → InterfaceBuilder
    → Default → ClassBuilder
    ↓
$builder->build($reflection, $params)
    → generiert instanceof MyClass
```

### 3.2 Strategy Pattern — Builder-Auswahl

`ClassBuilderService` (`src/Services/ClassBuilderService.php:19`) ist ein primitiver
Match-Dispatcher, der anhand von `ReflectionClass`-Attributen den passenden Builder
wählt. Alle Builder implementieren `ClassBuilderInterface` mit der Signatur:

```php
public function build(ReflectionClass $class, array $parameters): mixed;
```

### 3.3 Die vier Builder im Detail

#### ClassBuilder (`src/ClassBuilder/ClassBuilder.php`)

Der komplexeste Builder (340 Zeilen). Verarbeitet drei Fälle:

1. **Klasse ohne Konstruktor**: Erzeugt die Instanz direkt und setzt öffentliche
   Properties mit Zufallswerten.
2. **Klasse mit Konstruktor (public)**: Generiert für jeden Parameter einen Zufallswert
   über `DataTypeService` und übergibt diese als `newInstanceArgs()`.
3. **Klasse mit privatem Konstruktor**: Sucht nach statischen Methoden mit
   non-builtin-Rückgabetyp und ruft eine zufällig via `KlassenName::methode(...)` auf.
   Wenn das scheitert, greift der `tryExceptionSolver`.

**Wertgenerierung (`generateRandomValue`, Zeile 91)**:
1. Typ des Parameters via `ReflectionParameter::getType()` ermitteln
2. `DataTypeService::getDataTypeFromString()` parst den Typstring (Union-Types, Nullable-Types)
3. `DataTypeService::getDataTypeBuilder()` liefert den passenden `DataTypeInterface`-Builder
4. Falls der Typ ein bekannter Klassenname ist → rekursiver `ObjectBuilder::init()->build()`

**tryExceptionSolver (Zeile 228)**: Eine Recovery-Strategie, die PHP-Fehlermeldungen parst:
- Erkennt `Unknown or bad format (...)` → hat Hardcoded-Fixes für `DateInterval`/`DatePeriod`
- Erkennt `::__construct() accepts ...` → parst alle gültigen Parameter-Kombinationen
  aus der Fehlermeldung und probiert sie nacheinander durch

#### EnumBuilder (`src/ClassBuilder/EnumBuilder.php`)

Einfach: Holt alle Cases via `$enum::cases()`, wählt zufällig aus. Kann über
Parameter-Array auf bestimmte Werte gefiltert werden. Validiert dass die
übergebenen Werte tatsächlich existieren.

#### InterfaceBuilder (`src/ClassBuilder/InterfaceBuilder.php`)

Delegiert an einen zweiten Strategy-Layer: `HandlerService::getHandler()`.

#### TraitBuilder (`src/ClassBuilder/TraitBuilder.php`)

Der simpelste Builder: Erstellt eine anonyme Klasse via `eval()` die den
Trait `use`t. Parameter werden ignoriert.

### 3.4 Interface-Handler (Zweiter Strategy-Layer)

`HandlerService` (`src/ClassBuilder/Services/HandlerService.php:19`) wählt:

| Priorität | Handler | Bedingung |
|---|---|---|
| 1 | `ThrowableHandler` | Interface ist `Throwable` |
| 2 | `FileContentHandler` | Interface hat eine lesbare Quelldatei |
| 3 | `ImplementedClassHandler` | Es existieren deklarierte Implementierungen |
| — | Exception | Kein Handler passt |

#### FileContentHandler (`src/ClassBuilder/Interface/FileContentHandler.php`)

Der komplexeste Handler (276 Zeilen). Liest die Interface-Quelldatei Zeile für Zeile
und schreibt sie in eine konkrete Klasse um:

1. Ersetzt `interface Foo` → `class FooClass implements Foo`
2. Wandelt Konstruktor-Signaturen in leere Bodies um
3. Generiert für jede Methode einen Body, der einen zum Rückgabetyp passenden Wert
   zurückgibt:
   - Primitive Typen → via `DataTypeInterface::buildAsString()`
   - Objekt-Typen → `ObjectBuilder::init(Type::class)->build()`
   - Benutzerdefinierte Werte → serialisiert oder via `unserialize()`
4. **Infinity-Guard**: Zählt rekursive Selbstreferenz-Aufrufe und wirft
   `InfinityInterfaceException` nach 5 Rekursionen
5. **Namespace-Auflösung**: Sammelt `use`-Statements und löst sie relativ zum
   Namespace auf

#### ThrowableHandler

Spezial-Handler: Gibt `Exception` mit `previous => null` zurück (das
`Throwable`-Interface benötigt vorherige Exceptions, was sonst zur
Endlosrekursion führen würde).

#### ImplementedClassHandler

Findet alle deklarierten Klassen die das Interface implementieren (`class_implements`)
und instanziiert eine zufällige.

### 3.5 Datentypen-System

`DataTypeInterface` definiert drei Methoden:
- `build(): mixed` → erzeugt einen Wert
- `setProperty(Property): self` → setzt einen benutzerdefinierten Wert
- `buildAsString(): string` → erzeugt Wert als PHP-Code-String (für `eval()`)

| Builder | Output |
|---|---|
| `IntegerBuilder` | `mt_rand()` |
| `FloatBuilder` | `mt_rand() / mt_getrandmax()` |
| `StringBuilder` | Zufälliger alphanumerischer String (5-20 Zeichen) |
| `BooleanBuilder` | `(bool)mt_rand(0,1)` |
| `ArrayBuilder` | `['a' => 13]` |
| `MixedBuilder` | Zufälliger Wert aus int/float/string/bool/DateTimeImmutable/array/null |
| `CallbackBuilder` | `function($a,$b){return $a+$b;}` |
| `NullBuilder` | `null` |
| `SimpleObjectBuilder` | `(object)[]` |

**StringBuilder semantische Erkennung**:
- `timezone` → `timezone_identifiers_list()`
- `countrycode` → zufällig aus `['DE','EN','ES','FR']`
- `datetime` → zufälliges Datum im letzten Jahr

### 3.6 Typ-Parsing (`DataTypeService::getDataTypeFromString()`)

- `null` oder `?` → `['?']`
- `?int` → `['?', 'int']`
- `string|int` → `['string', 'int']`
- Andere → `['originalString']`

**Wichtig**: Der Caller (`generateRandomValue` in ClassBuilder) nimmt dann via
`array_rand()` einen zufälligen Wert aus dem Array. Das bedeutet: Bei `int|string`
wird zufällig gewürfelt, ob der Wert ein int ODER string ist — aber es wird nicht
das gesamte Optionsspektrum ausgereizt.

## 4. Was fehlt für vollständige Klassenbau-Fähigkeit?

### 4.1 Abstrakte Klassen

Stand: **Nicht unterstützt.** `ClassBuilderService` hat keinen `isAbstract()`-Check.
Ein `ObjectBuilder::init(AbstractClass::class)` würde entweder:
- Als normales ClassBuilder-Objekt behandelt werden und an `newInstanceArgs` scheitern
- Oder (wenn es keine Methode `newInstance` gibt) eine Exception werfen

**Lösungsansatz**: Ähnlich wie bei Interfaces müsste eine konkrete Subklasse
dynamisch erzeugt werden. Oder ähnlich dem ImplementedClassHandler könnte nach
existierenden Subklassen gesucht werden.

### 4.2 Intersection Types (`Countable&Iterator`)

Stand: **Nicht unterstützt.** `DataTypeService::getDataTypeFromString()` parst nur
`|` (Union), nicht `&` (Intersection). Ein `Countable&ArrayAccess`-Parameter würde
als kompletter Typ-String `Countable&ArrayAccess` an `getDataTypeBuilder` gehen
und `null` zurückbekommen → führt zu `ObjectBuilderDataTypeAndClassNotFoundException`.

### 4.3 `iterable` Pseudo-Type

Stand: **Nicht unterstützt.** `iterable` ist in `DataTypeService::getDataTypeBuilder`
nicht als Case aufgeführt. Es fällt auf den `default`-Zweig zurück und verursacht
eine Exception.

### 4.4 `resource` Type

Stand: **Nicht unterstützt.** Weder im DataTypeService noch gibt es einen
ResourceBuilder. Resources sind in PHP zunehmend obsolet (ersetzt durch Klassen in
PHP 8.x), kommen aber in Legacy-Code noch vor.

### 4.5 `void` und `never` Return-Types in Interfaces

Stand: **Fehlerhaft.** Wenn eine Interface-Methode `: void` deklariert, versucht
der `FileContentHandler` trotzdem einen Return-Value zu generieren (`{ return ...; }`),
was zu einem Syntax-Error im generierten Code führt. `never` wird gar nicht erkannt.

### 4.6 `static` Return-Type in Interface-Methoden

Stand: **Nicht unterstützt.** Der `FileContentHandler` kann `static` als Rückgabetyp
nicht auflösen und würde versuchen `ObjectBuilder::init('static')` aufzurufen.

### 4.7 `readonly` Classes (PHP 8.2)

Stand: **Nicht unterstützt.** Readonly-Klassen müssen im Konstruktor gesetzt werden.
Die Logik zur Property-Setzung nach Instanziierung (`setValue`) würde bei readonly
Properties fehlschlagen. Es gibt keinen dedizierten Check.

### 4.8 Enums ohne `::cases()` — `UnitEnum` / `IntBackedEnum` / reines `match`

Stand: **Teilweise unterstützt.** Der `EnumBuilder` ruft `$class->getName()::cases()`
auf, was für alle PHP-Enums funktioniert. Aber nicht alle Enum-Szenarien sind getestet.

### 4.9 PHP Built-In / Stock-Klassen

Stand: **Stark begrenzt.** Nur `DateInterval`, `DatePeriod` und `Exception` (via
ThrowableHandler) haben spezielle Behandlung. Der `StockClass.php`-Testentwurf listet
50+ PHP-eigene Klassen auf, die noch nicht getestet/unterstützt sind (Reflection*,
SPL-Iteratoren, PDO, Phar, Curl etc.). Das `tryExceptionSolver`-Verfahren ist auf
zwei harte Regex-Patterns limitiert und scheitert bei abweichenden Fehlermeldungen.

### 4.10 Klassen ohne Konstruktor mit privaten Properties + Setter

Stand: **Nicht unterstützt.** Der `ClassBuilder` setzt bei konstruktorlosen Klassen
nur **öffentliche** Properties. Private Properties mit Setter-Methoden (wie
die `Name`-Testklasse) werden ignoriert. Die `Name`-Klasse funktioniert nur, weil
sie via `Person`-Konstruktor als verschachteltes Argument gebaut wird.

### 4.11 `DateTimeImmutable`-spezifische Names

Stand: **Nicht erkannt.** Der `StringBuilder` erkennt `datetime` als Property-Namen
und liefert ein Datum als String. Aber wenn eine Property `DateTimeImmutable` als
Typ hat, wird der `MixedBuilder` oder ein zufälliger String-Builder verwendet
— nicht der DateTime-Builder.

### 4.12 Konfigurierbare Wertebereiche

Stand: **Nicht unterstützt.** Es gibt keine Möglichkeit, Wertebereiche anzugeben
(z.B. `age` soll zwischen 18 und 65 liegen, oder `email` soll eine gültige E-Mail
sein). Nur exakte Werte können überschrieben werden.

### 4.13 `self` und `parent` Type-Hints

Stand: **Nicht getestet / vermutlich nicht unterstützt.** Diese Typ-Hints würden
in `DataTypeService::getDataTypeBuilder` landen und nicht aufgelöst werden können.

### 4.14 Generierte Interface-Methoden ohne Parameter-Bodies

Stand: **Inkonsistent.** Der `FileContentHandler` generiert Methoden-Bodies, aber
wenn eine Interface-Methode selbst Parameter hat, werden diese nicht im Body
genutzt — es wird einfach ein statischer Rückgabewert generiert, egal was
übergeben wird. Das ist für Mocking-Zwecke oft akzeptabel, aber inkorrekt.

## 5. Bugs und technische Schulden

### 5.1 Aktive Bugs (aus den Tests ersichtlich)

1. **`testAndReturnValues` (Zeile 152, InterfaceBuilderTest.php)**: Test auf `MyInterface`
   ist mit `// todo dieser test geht nicht.` markiert — der Interface-Builder kann
   komplexe Interfaces mit `__construct`, statischen Methoden und mehreren
   Return-Types nicht korrekt umsetzen.

2. **`testMy` (Zeile 161, InterfaceBuilderTest.php)**: Gleicher Todo-Kommentar für
   `Selectable`-Interface.

### 5.2 Code-Qualität und technische Schulden

1. **Falscher Import**: `src/ClassBuilder/ClassBuilder.php:9` importiert
   `PHPUnit\Event\InvalidArgumentException` statt PHP's eigenem
   `InvalidArgumentException`. Das ist ein Produktionscode-Fehler — die
   Bibliothek sollte nicht von PHPUnit-Klassen abhängen.

2. **PHP Deprecation**: `DatePeriod::__construct()` erwartet `int` für Parameter
   `#4 ($options)`, aber `tryExceptionSolver` übergibt keinen vierten Parameter,
   was zu `null` führt. PHP 8.1 deprecated dies.

3. **`eval()` in Produktion**: Sowohl `TraitBuilder` als auch `FileContentHandler`
   verwenden `eval()` — ein bekanntes Sicherheitsrisiko für Code-Injection.
   `TraitBuilder` hat keine Input-Validierung des Trait-Namens.

4. **Statischer Zustand in `InterfaceBuilder`**: `InterfaceBuilder::$counter` ist
   ein statischer Zähler für den Infinity-Guard. Bei parallelen Tests oder
   mehrfachen Builds kann dies zu falschen `InfinityInterfaceException`-Würfen
   führen.

5. **Fehlende `declare(strict_types=1)`**: In `tests/ObjectBuilderTest.php` fehlt
   das `declare(strict_types=1)` Statement, das in allen anderen Dateien vorhanden ist.

6. **PHPStan nicht in `composer.json`**: Der `Makefile`-Target `phpstan` verweist
   auf `vendor/bin/phpstan`, aber `phpstan/phpstan` ist weder in `require` noch
   in `require-dev`. Der Befehl schlägt fehl, bis phpstan manuell installiert wird.

7. **ECS nicht in `composer.json`**: Gleiches Problem für `ecs.php` und die
   `symplify/easy-coding-standard`-Abhängigkeit.

8. **`getDefaultValue`-Methode ungenutzt**: In `ClassBuilder` Zeile 108 ist der
   Aufruf von `getDefaultValue` auskommentiert mit einem TODO-Kommentar. Die
   Methode ist implementiert, wird aber nie verwendet.

9. **Gemischte Kommentarsprachen**: Der Code enthält deutsche Kommentare
   (z.B. `// ToDo teste ob, wie und wann die Exception geworfen wird` in
   `ObjectBuilder.php:59` und `// silas versuche ein $parameterOptions` in
   `ClassBuilder.php:243`). Das erschwert Open-Source-Beiträge.

10. **Unverständliche Test-Namen**: `testTest` und `testTest2` in
    `ObjectBuilderTest.php` sind nichtssagende Namen. `testTest` enthält nur
    Debug-Code mit auskommentiertem Output.

11. **StringBuilder::randomCountryCode**: Nur 4 hartkodierte Länder-Codes,
    obwohl der verlinkte Packagist-Paket `aminkhoshzahmat/country-code` eine
    vollständige Länderliste hätte.

12. **Kein separater DateTime-Builder**: Der `MixedBuilder` erzeugt manchmal
    `DateTimeImmutable`, aber es gibt keinen dedizierten Builder der generisch
    für `DateTimeInterface`-Typen verwendet wird.

## 6. Container-Lösung (Step 1 Ergebnis)

Es wurde eine funktionierende Container-Umgebung erstellt:

- **Dockerfile** (`Dockerfile`): PHP 8.1 CLI, Extensions `intl`, `mbstring`,
  `pdo_mysql`, Composer 2
- **docker-compose.yml**: Services für `phpunit`, `phpstan`, `test-coverage`
- **Test-Ergebnis im Container**: 24 Tests, 71 Assertions, 0 Failures,
  1 Deprecation Notice

Die vollständigen Docker-Dateien liegen unter:
- `Dockerfile`
- `docker-compose.yml`
- `.dockerignore`

## 7. Test-Abdeckung

Die aktuelle Test-Suite deckt ab:
- [x] Einfache Klasse mit Konstruktor (`Address`)
- [x] Klasse mit privatem Konstruktor + statische Factory-Methoden (`PrivateConstruct`)
- [x] Klasse mit privatem Konstruktor OHNE statische Methoden → Exception
- [x] Verschachtelte Objektgraphen (`Person` → `Name`, `Address`, `MyEnum`, `DateTimeImmutable`, `MyInterface`)
- [x] Enum-Werte mit und ohne Filter
- [x] Traits mit Properties und Methoden
- [x] Leeres Interface
- [x] Interface ohne Return-Types
- [x] Interface mit primitiven Return-Types
- [x] Interface mit benutzerdefinierten Return-Werten
- [x] Interface mit Objekt-Rückgaben
- [x] Infinity-Interface-Abbruch
- [x] Stock-Klassen (`DateInterval`, `DatePeriod`, `StockClass`)
- [x] Benutzerdefinierte Parameter auf allen Ebenen
- [x] Null-Werte in Properties

Nicht abgedeckt:
- [ ] Abstrakte Klassen
- [ ] Intersection Types
- [ ] `iterable` / `resource` / `callable` im vollen Umfang
- [ ] `void`-/`never`-Return-Types in Interfaces
- [ ] Klassen ohne Konstruktor mit privaten Properties und Settern (direkt)
- [ ] `self`- und `parent`-Type-Hints
- [ ] Fehlerfälle für unbekannte Stock-Klassen
- [ ] PHPStan-Level-7-Check (nicht im CI ausgeführt)

## 8. Offene Fragen

1. **Soll das Projekt auf PHP 8.2+ gehoben werden?** Die aktuelle `^8.1`-Constraint
   schließt `readonly`-Klassen und andere 8.2-Features aus. Ein Upgrade würde
   readonly-Klassen, ENUM-Traits und standalone Typen ermöglichen.

2. **Ist `eval()` als Design-Entscheidung akzeptabel?** Oder sollte für Traits und
   Interfaces ein Code-Generator (z.B. `nikic/php-parser` der bereits in den
   Dev-Dependencies ist) verwendet werden, der AST-basiert arbeitet?

3. **Soll der `tryExceptionSolver` durch ein Plugin-System ersetzt werden?**
   Aktuell sind `DateInterval` und `DatePeriod` hartkodiert. Ein
   `StockClassHandler`-Interface würde es Nutzern erlauben, eigene Handler
   für problematische Built-In-Klassen zu registrieren.

4. **Wie soll mit abstrakten Klassen umgegangen werden?** Sollen existierende
   Subklassen gesucht werden (wie ImplementedClassHandler) oder anonyme
   Subklassen generiert werden?

5. **Sollen Value-Constraints unterstützt werden?** Z.B. `['age' => ['min' => 18, 'max' => 65]]`
   oder `['email' => ['format' => 'email']]`. Das würde den Nutzen für
   realistischere Testdaten erheblich steigern.

6. **Ist ein PSR-11 Container-Interface gewünscht?** Die Builder könnten
   austauschbar sein, und die `DataTypeService`-Mappings erweiterbar.

7. **Welche PHP-Stock-Klassen haben Priorität?** Die `StockClass`-Testdatei
   listet 50+ Klassen. Welche davon sollen als erstes unterstützt werden?

## 9. Zusammenfassung und Empfehlungen

Der ObjectBuilder ist ein funktionsfähiges, in Teilen beeindruckend ausgeklügeltes Werkzeug
(die Source-File-Parsing-Engine für Interfaces ist kreativ und mächtig). Es hat jedoch
erkennbare Lücken und technische Schulden, die den Anspruch "vollständig" einschränken.

**Empfohlene Prioritäten für die Weiterentwicklung:**

1. **Kritische Bugs fixen**: Falscher PHPUnit-Import in `ClassBuilder.php`,
   `DatePeriod`-Deprecation
2. **Fehlende PHP-Features nachrüsten**: `iterable`-Support, `void`/`never`-Handling
   in Interfaces, `readonly`-Klassen (falls PHP 8.2)
3. **Abstrakte Klassen**: ClassBuilderService um `isAbstract()` erweitern
4. **`eval()` durch AST ersetzen**: `nikic/php-parser` ist bereits in den
   Dev-Dependencies
5. **Plugin-System für Stock-Klassen**: Austauschbare Handler statt hartkodiertem
   `tryExceptionSolver`
6. **Testabdeckung erhöhen**: Die beiden fehlschlagenden Interface-Tests
   (`MyInterface`, `Selectable`) fixen, plus Tests für abstrakte Klassen,
   Intersection-Types, `iterable`, etc.
7. **Dokumentation vervollständigen**: README um fortgeschrittene Features
   erweitern, IDE-Hints durch PHP-Doc verbessern
