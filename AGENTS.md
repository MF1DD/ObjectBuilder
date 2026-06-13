# AGENTS.md — ObjectBuilder

> Leitfaden für KI-Agenten, die an diesem Projekt arbeiten.

## Projekt-Übersicht

**ObjectBuilder** ist eine PHP 8.2+-Bibliothek, die automatisch vollständig
befüllte Objekt-Instanzen mit Zufallswerten erzeugt. Primäres Einsatzgebiet:
Test-Fixtures. Sekundär: Runtime-Objektgenerierung.

- **Package**: `mf1dd/object-builder`
- **Runtime-Dep**: `nikic/php-parser ^5.0`
- **Kein Framework** (kein Symfony, Laravel, etc.)

## Architektur — 4-Layer Hexagonal

Das Projekt folgt einer Clean-Architecture mit 4 Layern. Abhängigkeiten
dürfen nur von oben nach unten fließen:

```
UserInterface  (MF1DD\UserInterface\)
    ↓ darf abhängen von
Application    (MF1DD\Application\)
    ↓ darf abhängen von
Domain         (MF1DD\Domain\)
    ↓ (niemand hängt davon ab, es ist das Fundament)

Infrastructure (MF1DD\Infrastructure\)
    → darf abhängen von Application + Domain
```

**Deptrac überwacht diese Regeln.** Jeder Commit wird in der CI geprüft.
→ `make deptrac`

### Layer-Inhalte

| Layer | Verzeichnis | Enthält |
|---|---|---|
| **Domain** | `src/Domain/` | Interfaces (`ClassBuilderInterface`, `DataTypeInterface`, `HandlerInterface`, `StockClassHandlerInterface`), DTOs (`Property`, `Constraints`, `NoValueSet`), alle Exceptions |
| **Application** | `src/Application/` | Builder-Klassen (`ClassBuilder`, `EnumBuilder`, `InterfaceBuilder`, `TraitBuilder`, `AbstractClassBuilder`), Services (`ClassBuilderService`, `DataTypeService`, `StockClassHandlerService`, `HandlerService`, `ObjectBuildService`), Interface-Handler (`FileContentHandler`, `ImplementedClassHandler`, `ThrowableHandler`) |
| **Infrastructure** | `src/Infrastructure/` | DataType-Implementierungen (`ArrayBuilder`, `BooleanBuilder`, `StringBuilder` …), StockClass-Handler (`DateIntervalHandler`, `DateTimeImmutableHandler` …) |
| **UserInterface** | `src/UserInterface/` | `ObjectBuilder` (öffentliche Facade) |

### Objekt-Bau-Fluss

```
User: ObjectBuilder::init(Foo::class)->build()
  → ObjectBuilder (UserInterface) delegiert an
  → ObjectBuildService (Application)
    → ClassBuilderService::getClassBuilder($reflection)
      → Enum?   → EnumBuilder
      → Trait?  → TraitBuilder
      → Interface? → InterfaceBuilder
      → Abstract? → AbstractClassBuilder
      → default → ClassBuilder
    → $builder->build($reflection, $params, $constraints)
      → Reflection: prüft Constructor, Properties
      → DataTypeService: wählt passenden DataType-Builder
      → Rekursion: für verschachtelte Objekte
```

## Verzeichnisstruktur

```
src/
├── Domain/
│   ├── Dto/           (Property, Constraints, NoValueSet, …)
│   ├── Exceptions/    (11 Exception-Klassen)
│   └── *.php          (4 Interfaces)
├── Application/
│   ├── Interface/     (FileContentHandler, ThrowableHandler, …)
│   ├── Services/      (ClassBuilderService, DataTypeService, …)
│   └── *.php          (5 Builder-Klassen)
├── Infrastructure/
│   ├── StockClass/    (DateIntervalHandler, …)
│   └── *.php          (9 DataType-Implementierungen)
└── UserInterface/
    └── ObjectBuilder.php

tests/
├── Helper/            (22 Fixture-Klassen: Person, Address, …)
├── Unit/              (spiegelt src/-Struktur)
│   ├── Application/
│   ├── Domain/
│   └── Infrastructure/
└── Integration/
    └── ObjectBuilderTest.php
```

**Wichtig**: Tests spiegeln exakt die `src/`-Struktur. Test-Namespace =
`MF1DD\Tests\` + selber relativer Pfad wie Source. Beispiel:
- Source: `MF1DD\Domain\Dto\Constraints` → `src/Domain/Dto/Constraints.php`
- Test: `MF1DD\Tests\Domain\Dto\ConstraintsTest` → `tests/Unit/Domain/Dto/ConstraintsTest.php`

## Coding-Konventionen

- `declare(strict_types=1)` in **jeder** PHP-Datei
- PHP 8.2+ Features erlaubt: `readonly` classes, named arguments, match, enums
- `#[Override]`-Attribut nur, wenn PHP 8.3+ verfügbar (derzeit nicht genutzt)
- Properties: `private` oder `private readonly` — kein `public`
- Methoden: kein `static` außer bei Factories/Services
- Keine deutschen Kommentare im Code
- Typ-Hints auf allen Methoden (Rückgabe + Parameter)
- PHPDoc für generische Typen (`array<string, mixed>`, `ReflectionClass<object>`)

## Wichtige Klassen

| Klasse | Rolle |
|---|---|
| `ObjectBuilder` | Öffentliche Facade, UserInterface-Layer |
| `ObjectBuildService` | Orchestriert den Bau-Prozess, Application-Layer |
| `ClassBuilderService` | Wählt den richtigen Builder via Reflection |
| `ClassBuilder` | Baut reguläre Klassen (mit/ohne Constructor, private Constructor) |
| `EnumBuilder` | Baut Enum-Werte (optional gefiltert) |
| `InterfaceBuilder` | Delegiert an HandlerService für Interface-Implementierung |
| `FileContentHandler` | Liest Interface-Quellcode, generiert AST-basiert implementierende Klasse |
| `DataTypeService` | Mapped Typ-Strings (`'int'`, `'string\|bool'`) auf Builder |
| `DataTypeInterface` | Interface für alle Typ-Builder (build, setProperty, buildAsString) |
| `StockClassHandlerService` | Verwaltet Handler für PHP-Builtin-Klassen (DateInterval, etc.) |

## Qualitäts-Tools

Alle Konfigurationen liegen unter `.qa/{toolname}/`:

| Tool | Config | Befehl |
|---|---|---|
| PHPStan L7 | `.qa/phpstan/phpstan.neon` | `make phpstan` |
| Psalm L8 | `.qa/psalm/psalm.xml` | `make psalm` |
| Deptrac | `.qa/deptrac/deptrac.yaml` | `make deptrac` |
| Rector | `.qa/rector/rector.php` | `make rector` |
| Infection | `.qa/infection/infection.json5` | `make infection` |

Alle Befehle laufen via `make <target>` — der Container wird automatisch gestartet.

**CI-Pipeline** (`.github/workflows/test.yml`): 7 Jobs auf PHP 8.2–8.5.

## Häufige AI-Aufgaben

### Neue Klasse hinzufügen

1. Klasse in passenden Layer unter `src/{Layer}/` erstellen
2. Namespace: `MF1DD\{Layer}\[Subpfad]`
3. Test in `tests/Unit/{Layer}/[Subpfad]/KlassennameTest.php` erstellen
4. Namespace: `MF1DD\Tests\{Layer}\[Subpfad]`
5. `make test` — alle 104 Tests müssen grün sein
6. `make phpstan` und `make psalm` — 0 Errors
7. `make deptrac` — 0 Violations (keine verbotenen Layer-Abhängigkeiten!)

### Bestehenden Code ändern

1. Branch: `git checkout -b feature-name`
2. Änderung umsetzen
3. `make test` + `make phpstan` + `make psalm` + `make deptrac`
4. Bei Layer-übergreifenden Änderungen: Deptrac prüft Abhängigkeiten automatisch
5. Commit, push, merge

### Deptrac-Verstoß beheben

1. `make deptrac` zeigt die verbotene Abhängigkeit
2. **Niemals** die Baseline erweitern — den Code fixen
3. Typische Lösung: Service extrahieren (wie `ObjectBuildService`)
4. Oder: `::class`-Referenzen durch String-Literale ersetzen (nur Compile-Time-Abhängigkeiten zählen)

### Test-Abdeckung verbessern

1. `docker-compose run --rm -T -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-text`
2. Unabgedeckte Zeilen identifizieren
3. Test in der passenden Testklasse unter `tests/Unit/` hinzufügen
4. Keine Sammel-Testklassen (kein `MutationKillerTest`, kein `FullCoverageTest`)

## Constraints

- **Keine** `eval()`-Aufrufe (alle durch temp-file+include oder AST ersetzt)
- **Keine** Application→UserInterface Compile-Time-Abhängigkeiten
- **Keine** zirkulären Abhängigkeiten zwischen Layern
- **Keine** neuen Sammel-Testdateien — jeder Source-Klasse ihre eigene Testklasse
- Exceptions und DTOs sind von Infection-Mutation ausgeschlossen
- Keine globalen Functions — alles in Klassen
