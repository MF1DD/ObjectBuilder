# ObjectBuilder — Strategic Implementation Plan (ToDo.md)

> Abgeleitet aus der Analyse in `Report.md`, den 7 offenen Fragen und den Empfehlungen.
> Ziel: Ein ObjectBuilder, der restlos **alle** Arten von PHP-Klassen bauen kann.

## Arbeitsablauf pro Phase

Jede Phase wird nach folgendem Git-Workflow abgearbeitet:

1. **Branch erstellen**: `git checkout -b phase-X-description` (ausgehend von `main`)
2. **Implementieren**: Änderungen in logischen Einheiten umsetzen
3. **Qualität sicherstellen**: `vendor/bin/phpunit` muss grün sein, `vendor/bin/phpstan analyse` muss sauber sein
4. **Commit & Push**: `git add` + `git commit` + `git push -u origin phase-X-description`
5. **Merge**: `git checkout main && git merge phase-X-description && git push`
6. **Nächste Phase**: Zurück zu Schritt 1

**Wichtig**: Am Ende jeder Phase muss das Projekt mindestens so gut laufen wie zuvor.
Code-Qualität, Tests und statische Analyse dürfen sich nicht verschlechtern.

> Abgeleitet aus der Analyse in `Report.md`, den 7 offenen Fragen und den Empfehlungen.
> Ziel: Ein ObjectBuilder, der restlos **alle** Arten von PHP-Klassen bauen kann.

---

## Abhängigkeitsgraph der Phasen

```
Phase 0  (Bugfixes)
   ↓
Phase 1  (PHP 8.2+ Upgrade) ←── Voraussetzung für Phase 4, 5, 6
   ↓
Phase 2  (Plugin-System Stock-Klassen) ←── Voraussetzung für Phase 7
   ↓
Phase 3  (eval() → nikic/php-parser)
   ↓
Phase 4  (Fehlende PHP-Types: iterable, void, never, intersection, self/static)
   ↓
Phase 5  (Value Constraints: min/max, email, url, length)
   ↓
Phase 6  (PSR-11 Container, swappable builder, extensible types)
   ↓
Phase 7  (Alle Stock-Klassen: Reflection*, SPL, PDO, Phar, Curl, ...)
   ↓
Phase 8  (Abstrakte Klassen)
   ↓
Phase 9  (Test-Abdeckung, Dokumentation, Cleanup)
```

---

## Phase 0 — Bugfixes & technische Schulden beseitigen

> Keine Abhängigkeiten. Kann sofort begonnen werden.

### 0.1 Falschen PHPUnit-Import fixen
- **Datei**: `src/ClassBuilder/ClassBuilder.php:9`
- **Aktion**: `use PHPUnit\Event\InvalidArgumentException;` ersetzen durch `use InvalidArgumentException;`
- **Test**: `vendor/bin/phpunit` — alle Tests müssen weiter grün sein

### 0.2 DatePeriod-Deprecation fixen
- **Datei**: `src/ClassBuilder/ClassBuilder.php:293`
- **Aktion**: In `tryExceptionSolver` den vierten Parameter `$options` als `0` (int) setzen statt `null`
- **Test**: `vendor/bin/phpunit` — Deprecation-Warning muss verschwinden

### 0.3 `declare(strict_types=1)` in allen Test-Dateien ergänzen
- **Datei**: `tests/ObjectBuilderTest.php` (und alle anderen ohne)
- **Aktion**: `declare(strict_types=1);` nach `<?php` einfügen

### 0.4 phpstan & ecs in composer.json als dev-dependencies ergänzen
- **Datei**: `composer.json`
- **Aktion**: `"phpstan/phpstan": "^1.10"` und `"symplify/easy-coding-standard": "^12"` zu `require-dev` hinzufügen
- **Nacharbeit**: `composer update` ausführen, `vendor/bin/phpstan analyse` und `vendor/bin/ecs check src tests` testen

### 0.5 `getDefaultValue` implementieren oder entfernen
- **Datei**: `src/ClassBuilder/ClassBuilder.php:107-108`
- **Aktion**: Entweder den auskommentierten Code aktivieren und testen, oder die Methode `getDefaultValue` (Zeile 211-222) entfernen
- **Entscheidung**: Aktivieren — Default-Werte aus Reflection sind nützlich für realistische Fixtures

### 0.6 StringBuilder::randomCountryCode() erweitern
- **Datei**: `src/DataTypes/StringBuilder.php:76-83`
- **Aktion**: Alle ISO-3166-Ländercodes aus `\Sokil\IsoCodes\IsoCodesFactory` oder einer statischen Liste verwenden. Minimum: Top 50 Länder.

---

## Phase 1 — PHP 8.2+ Upgrade

> Nutzer-Priorität #1. Ermöglicht readonly-Klassen und standalone types.

### 1.1 composer.json PHP-Version anheben
- **Datei**: `composer.json`
- **Aktion**: `"php": "^8.1"` → `"php": "^8.2"`
- **Warum**: Readonly-Klassen (PHP 8.2), `readonly` in Konstruktor-Promotion verbessert, standalone types erlauben `null|false` etc.

### 1.2 Dockerfile auf PHP 8.2 umstellen
- **Datei**: `Dockerfile`
- **Aktion**: `FROM php:8.1-cli` → `FROM php:8.2-cli`

### 1.3 CI/CD Pipeline updaten
- **Datei**: `.github/workflows/test.yml`
- **Aktion**: `php-version: '8.1'` → `'8.2'`

### 1.4 Readonly-Klassen-Support implementieren
- **Datei**: `src/ClassBuilder/ClassBuilder.php` (neue Methode oder Logik in `build()`)
- **Anforderung**: Wenn `$class->isReadOnly()`, muss sichergestellt werden, dass alle Properties im Konstruktor gesetzt werden, nicht via `setValue()` danach
- **Test**: Neue Test-Klasse mit `readonly class TestReadOnly { ... }` erstellen

---

## Phase 2 — Plugin-System für Stock-Klassen

> Nutzer-Priorität #2. Ersetzt den fragilen `tryExceptionSolver`.

### 2.1 StockClassHandlerInterface definieren
- **Neue Datei**: `src/ClassBuilder/Interface/StockClassHandlerInterface.php`
- **Signatur**:
  ```php
  interface StockClassHandlerInterface
  {
      public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object;
      public static function supports(ReflectionClass $class): bool;
  }
  ```

### 2.2 DateIntervalHandler implementieren
- **Neue Datei**: `src/ClassBuilder/Interface/StockClass/DateIntervalHandler.php`
- **Logik**: Extrahiert aus `tryExceptionSolver` (Zeile 233-237) — `new DateInterval('P7D')`
- **Test**: Aus bestehenden Tests ableiten

### 2.3 DatePeriodHandler implementieren
- **Neue Datei**: `src/ClassBuilder/Interface/StockClass/DatePeriodHandler.php`
- **Logik**: Extrahiert aus `tryExceptionSolver` (Zeile 234-235) — `new DatePeriod(...)`
- **Achtung**: Parameter #4 korrekt als `int` setzen (siehe Phase 0.2)

### 2.4 HandlerService / neuen StockClassHandlerService erstellen
- **Neue Datei**: `src/Services/StockClassHandlerService.php`
- **Logik**: Registriert alle StockClassHandler, dispatched nach `supports()`-Check
- **Registry**: Array von Handler-Klassen, austauschbar (Vorbereitung für Phase 6)

### 2.5 tryExceptionSolver durch Plugin-Dispatch ersetzen
- **Datei**: `src/ClassBuilder/ClassBuilder.php` (Zeile 228-309)
- **Aktion**: Die gesamte `tryExceptionSolver`-Methode durch Aufruf von `StockClassHandlerService::handle()` ersetzen
- **Fallback**: Wenn kein Handler matched → `UnknownOrBadFormatNotDeclaredClassException`

### 2.6 Tests für das Plugin-System
- **Neue Datei**: `tests/ClassBuilder/StockClassHandlerTest.php`
- **Inhalt**: Testen, dass DateIntervalHandler und DatePeriodHandler korrekt arbeiten, dass unbekannte Klassen die richtige Exception werfen, dass benutzerdefinierte Handler registriert werden können

---

## Phase 3 — `eval()` durch `nikic/php-parser` (AST) ersetzen

> Nutzer-Priorität #3. Sicherheit & Wartbarkeit.

### 3.1 TraitBuilder auf AST umstellen
- **Datei**: `src/ClassBuilder/TraitBuilder.php`
- **Aktion**: `eval()` ersetzen durch AST-basierte Code-Generierung mit `nikic/php-parser`
- **Vorteil**: Keine Code-Injection möglich, bessere Fehlermeldungen

### 3.2 FileContentHandler auf AST umstellen
- **Datei**: `src/ClassBuilder/Interface/FileContentHandler.php`
- **Aktion**: Zeilenweises String-Parsing ersetzen durch AST-Traversal:
  - `PhpParser\Parser` zum Parsen des Interface-Quellcodes
  - `PhpParser\NodeTraverser` + `NodeVisitor` zum Umbauen:
    - `Stmt\Interface_` → `Stmt\Class_` mit `implements`
    - `Stmt\ClassMethod` mit Return-Type → Body mit Return-Statement generieren
    - `Stmt\ClassConst` erhalten
- **Achtung**: Die Logik muss exakt dasselbe Verhalten wie das aktuelle String-Parsing liefern
- **Test**: Alle existierenden Interface-Tests müssen grün bleiben

---

## Phase 4 — Fehlende PHP-Type-Unterstützung

> Grundlage für vollständige Typ-Abdeckung.

### 4.1 `iterable` Pseudo-Type unterstützen
- **Datei**: `src/Services/DataTypeService.php:22`
- **Aktion**: `'iterable' => new ArrayBuilder()` als Case hinzufügen
- **Datei**: `src/DataTypes/ArrayBuilder.php`
- **Aktion**: ArrayBuilder so erweitern, dass er auch `Traversable`-Objekte (z.B. `ArrayIterator`) generieren kann (für iterable-kompatible Rückgaben)

### 4.2 `void`-Return-Type in Interface-Methoden handhaben
- **Datei**: `src/ClassBuilder/Interface/FileContentHandler.php` → nach Phase 3 in AST-Node-Visitor
- **Aktion**: `void` → Methoden-Body ohne Return-Statement; `never` → `throw new \RuntimeException('Not implemented')`
- **Test**: Neue Interface-Testklasse mit `function doSomething(): void;`

### 4.3 `never`-Return-Type handhaben
- Gleiche Datei wie 4.2
- **Aktion**: `never` → `{ throw new \RuntimeException('Method should never be called'); }`

### 4.4 Intersection Types (`&`) parsen
- **Datei**: `src/Services/DataTypeService.php:40-49`
- **Aktion**: `getDataTypeFromString()` um `&`-Parsing erweitern, ähnlich wie `|`
- **Datei**: `src/ClassBuilder/ClassBuilder.php` → `generateRandomValue()`
- **Aktion**: Bei Intersection-Types muss ein Objekt gebaut werden, das alle Interfaces implementiert

### 4.5 `self` / `static` / `parent` Type-Hints auflösen
- **Datei**: `src/ClassBuilder/ClassBuilder.php` → `generateRandomValue()`
- **Aktion**: `self` und `static` durch den aktuellen Klassennamen ersetzen und rekursiv behandeln
- **Datei**: `src/ClassBuilder/Interface/FileContentHandler.php`
- **Aktion**: `static` im Interface-Kontext durch den generierten Klassennamen ersetzen

---

## Phase 5 — Value Constraints (Wertebereiche)

> Nutzer-Priorität #5. "Je einfacher für die Tests, umso besser."

### 5.1 Constraint-DTO definieren
- **Neue Datei**: `src/Dto/Constraints.php`
- **Properties**: `?int $min`, `?int $max`, `?int $length`, `?string $format` (email, url, uuid, ip, date, datetime), `?array $enum` (whitelist), `?bool $nullable`

### 5.2 Constraints in Property-DTO integrieren
- **Datei**: `src/Dto/Property.php`
- **Aktion**: Optionales `?Constraints $constraints`-Feld hinzufügen

### 5.3 ObjectBuilder::init() um Constraints-API erweitern
- **Datei**: `src/ObjectBuilder.php`
- **Aktion**: Zweiten optionalen Parameter `array $constraints` oder `.withConstraint()`-Fluent-Methode
- **API-Beispiel**:
  ```php
  ObjectBuilder::init(Person::class)
      ->with('age', ['min' => 18, 'max' => 65])
      ->with('email', ['format' => 'email'])
      ->build();
  ```

### 5.4 DataTypeBuilder um Constraints erweitern
- **Alle Dateien in**: `src/DataTypes/*.php`
- **Aktion**: `build()`-Methoden prüfen `$this->property->constraints` und wenden sie an:
  - `IntegerBuilder`: `mt_rand($min, $max)`
  - `FloatBuilder`: `$min + mt_rand() / mt_getrandmax() * ($max - $min)`
  - `StringBuilder`: Email-Generator, URL-Generator, UUID (`uuid_create()`), Längenbegrenzung

### 5.5 Semantische String-Namen erweitern
- **Datei**: `src/DataTypes/StringBuilder.php:48-54`
- **Aktion**: Neue Cases: `email` → generierte E-Mail, `url` → generierte URL, `phone` → Telefonnummer, `uuid` → UUID, `ip` → IP-Adresse, `firstname` → Vorname, `lastname` → Nachname, `city` → Stadt, `street` → Straße, `zip` → PLZ

---

## Phase 6 — PSR-11 Container & erweiterbare Architektur

> Nutzer-Priorität #6. Macht alle Komponenten austauschbar.

### 6.1 BuilderRegistry per Container
- **Neue Datei**: `src/Services/BuilderRegistry.php`
- **Funktion**: Zentrales Registry für alle Builder (ClassBuilder, EnumBuilder, etc.)
- **Interface**: `register(string $type, ClassBuilderInterface $builder): void`

### 6.2 DataTypeService erweiterbar machen
- **Datei**: `src/Services/DataTypeService.php`
- **Aktion**: Statische `match`-Map durch eine injizierbare Registry ersetzen
- **API**: `DataTypeService::register(string $typeName, DataTypeInterface $builder): void`

### 6.3 StockClassHandlerService erweiterbar machen
- **Datei**: `src/Services/StockClassHandlerService.php` (aus Phase 2)
- **Aktion**: `register(StockClassHandlerInterface $handler)` Methode

### 6.4 ObjectBuilder um Builder-Injection erweitern
- **Datei**: `src/ObjectBuilder.php`
- **Aktion**: Optionalen Parameter für einen PSR-11 Container oder Builder-Overrides
- **API**: `ObjectBuilder::init(...)->withBuilder('class', $myCustomBuilder)->build()`

---

## Phase 7 — Alle PHP Stock-Klassen unterstützen

> Nutzer-Priorität #7. "Bevor die Liste nicht abgearbeitet ist, ist das Tool nicht fertig."

### 7.1 Stock-Klassen kategorisieren
- **Datei**: `tests/ClassBuilder/Helper/StockClass.php` (als Referenz)
- **Kategorien**:
  1. **Reflection** (12+ Klassen): ReflectionClass, ReflectionMethod, ReflectionProperty, ReflectionParameter, ReflectionType, ReflectionNamedType, ReflectionUnionType, ReflectionIntersectionType, ReflectionFunction, ReflectionFunctionAbstract, ReflectionObject, ReflectionExtension, ReflectionAttribute, ReflectionEnum, ReflectionEnumUnitCase, ReflectionEnumBackedCase, ReflectionReference, ReflectionFiber, ReflectionClassConstant, ReflectionZendExtension
  2. **SPL Iterators** (20+ Klassen): ArrayObject, ArrayIterator, RecursiveArrayIterator, SplFileInfo, DirectoryIterator, FilesystemIterator, RecursiveDirectoryIterator, GlobIterator, SplFileObject, SplTempFileObject, SplDoublyLinkedList, SplQueue, SplStack, SplHeap, SplMinHeap, SplMaxHeap, SplPriorityQueue, SplFixedArray, SplObjectStorage, MultipleIterator, RecursiveIteratorIterator, IteratorIterator, FilterIterator, RecursiveFilterIterator, CallbackFilterIterator, RecursiveCallbackFilterIterator, ParentIterator, LimitIterator, CachingIterator, RecursiveCachingIterator, NoRewindIterator, AppendIterator, InfiniteIterator, RegexIterator, RecursiveRegexIterator, EmptyIterator, RecursiveTreeIterator
  3. **PDO** (4 Klassen): PDO, PDOStatement, PDORow, PDOException
  4. **Phar** (4 Klassen): Phar, PharData, PharFileInfo, PharException
  5. **Curl**: CURLStringFile, CurlHandle, CurlMultiHandle, CurlShareHandle
  6. **Sonstige**: Directory, php_user_filter, __PHP_Incomplete_Class, AssertionError, SessionHandler, SessionHandlerInterface

### 7.2 Handler für jede Stock-Klasse erstellen
- **Verzeichnis**: `src/ClassBuilder/Interface/StockClass/`
- **Pro Kategorie ein Handler** oder bei komplexen Klassen ein eigener Handler
- **Reihenfolge**: Reflection → SPL → PDO → Phar → Curl → Sonstige

### 7.3 Tests für alle Stock-Klassen
- **Neue Datei**: `tests/ClassBuilder/StockClassHandlersTest.php`
- **Referenz**: `tests/ClassBuilder/Helper/StockClass.php` — alle auskommentierten Klassen aktivieren
- **Assertion**: Jeder Handler muss eine gültige Instanz des jeweiligen Typs liefern

---

## Phase 8 — Abstrakte Klassen

> Nutzer-Priorität #4. "Später kommen wir zu den abstrakten Klassen zurück."

### 8.1 ClassBuilderService um `isAbstract()` erweitern
- **Datei**: `src/Services/ClassBuilderService.php:19-27`
- **Aktion**: Neuer Case im Match: `$reflection->isAbstract() => new AbstractClassBuilder()`

### 8.2 AbstractClassBuilder implementieren
- **Neue Datei**: `src/ClassBuilder/AbstractClassBuilder.php`
- **Logik**: Ähnlich wie InterfaceBuilder — zwei Strategien:
  - Existierende Subklassen finden (via `get_declared_classes` + `is_subclass_of`) → zufällige wählen
  - Fallback: Abstrakte Klasse parsen und konkrete Subklasse dynamisch generieren (wie FileContentHandler für Interfaces)

### 8.3 Tests für abstrakte Klassen
- **Neue Datei**: `tests/ClassBuilder/Helper/Entity/AbstractEntity.php`
- **Test**: `ObjectBuilder::init(AbstractEntity::class)->build()` muss eine konkrete Instanz liefern

---

## Phase 9 — Test-Abdeckung, Dokumentation & Cleanup

### 9.1 Fehlschlagende Interface-Tests fixen
- **Datei**: `tests/ClassBuilder/InterfaceBuilderTest.php` (Zeile 152-168)
- **Tests**: `testAndReturnValues` und `testMy` — Ursache analysieren und beheben
- **Abhängigkeit**: Wird vermutlich durch Phase 3 (AST) und Phase 4 (void/static-Types) gefixt

### 9.2 Test-Abdeckung auf >90% bringen
- Alle neuen Features aus Phase 2-8 mit Tests abdecken
- Edge-Cases: Null-Werte, leere Arrays, zirkuläre Referenzen, tiefe Objektgraphen
- Negative Tests: Ungültige Klassen, falsche Parameter-Typen

### 9.3 Deutsche Kommentare ins Englische übersetzen
- **Betroffene Dateien**:
  - `src/ObjectBuilder.php:59` — `// ToDo teste ob, wie und wann die Exception geworfen wird`
  - `src/ClassBuilder/ClassBuilder.php:42-50, 83, 107-108, 205, 243`
  - `tests/ObjectBuilderTest.php:88-103`
- **Aktion**: Alle deutschen Kommentare durch englische ersetzen

### 9.4 README.md vervollständigen
- **Datei**: `README.md`
- **Aktion**: Neue Features dokumentieren:
  - Constraints API (Phase 5)
  - Stock-Klassen-Support (Phase 7)
  - Benutzerdefinierte Handler registrieren (Phase 6)
  - Abstrakte Klassen (Phase 8)
  - Vollständige Liste unterstützter Typen

### 9.5 CHANGELOG.md erstellen
- **Neue Datei**: `CHANGELOG.md`
- **Inhalt**: Alle Änderungen pro Phase dokumentiert

---

## Zusammenfassung der Phasen-Reihenfolge

| # | Phase | Grund für Position |
|---|---|---|
| 0 | Bugfixes & technische Schulden | Keine Abhängigkeiten, räumt den Weg frei |
| 1 | PHP 8.2+ Upgrade | Voraussetzung für readonly-Klassen, verbesserte Typ-Features |
| 2 | Plugin-System Stock-Klassen | Ersetzt fragilen tryExceptionSolver, Voraussetzung für Phase 7 |
| 3 | eval() → nikic/php-parser | Sicherheit & Wartbarkeit, betrifft TraitBuilder + FileContentHandler |
| 4 | Fehlende PHP-Types | Grundlage für Vollständigkeit, einfacher nach PHP 8.2 |
| 5 | Value Constraints | Unabhängig von 2-4, hoher Nutzwert für Tests |
| 6 | PSR-11 Container | Baut auf stabiler Architektur aus Phase 2-4 auf |
| 7 | Alle Stock-Klassen | Hängt vom Plugin-System (Phase 2) ab |
| 8 | Abstrakte Klassen | Nutzerwunsch: erst später |
| 9 | Polish & Doku | Finalisierung nach allen Features |

## Parallelisierbare Arbeiten

Folgende Phasen(-teile) können **gleichzeitig** von mehreren Agenten bearbeitet werden:

- **Phase 0**: Alle 6 Tasks sind unabhängig → parallel
- **Phase 2 + Phase 4**: Plugin-System (2.1-2.3) und Typ-Unterstützung (4.1-4.4) sind unabhängig
- **Phase 5 + Phase 7**: Value Constraints (5.1-5.5) und Stock-Klassen (7.1-7.3) können parallel laufen, sobald Phase 2 abgeschlossen ist
- **Phase 7 intern**: Jede Stock-Klassen-Kategorie (Reflection, SPL, PDO, Phar, Curl) kann parallel gebaut werden

## Offene Design-Fragen (zur Klärung vor/nach bestimmten Phasen)

1. **Phase 1**: Soll das PHP-Minimum auf 8.2 oder sogar 8.3? (8.3 bietet `json_validate()`, `mb_str_pad()`, `\Random\Randomizer` — für bessere Zufallswerte relevant)
2. **Phase 2**: Soll der `tryExceptionSolver` komplett entfernt oder bis zum Abschluss von Phase 7 als Fallback erhalten bleiben?
3. **Phase 3**: Reicht `nikic/php-parser` oder soll ein eigener, schlankerer PHP-Code-Generator gebaut werden?
4. **Phase 5**: Constraints als eigenes DTO oder direkt in `Property` integrieren?
5. **Phase 6**: Welches PSR-11 Package? `psr/container` als Interface + eigene Implementierung oder ein bestehendes wie `league/container`?
