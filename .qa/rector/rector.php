<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../src',
        __DIR__ . '/../../tests',
    ])
    ->withPhpSets(php82: true)
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
    ])
    ->withSkip([
        ReadOnlyClassRector::class,
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [
            __DIR__ . '/../../src/Application/Interface/FileContentHandler.php',
        ],
    ]);
