<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
            __FILE__,
        ],
    );

    $ecsConfig->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
        PhpdocAlignFixer::class,
        PhpdocToCommentFixer::class => 'src/Entity',
        NoSuperfluousPhpdocTagsFixer::class,
    ]);

    $ecsConfig->sets(
        [
            SetList::PSR_12,
            SetList::CLEAN_CODE,
            SetList::ARRAY,
            SetList::STRICT,
            SetList::SPACES,
            SetList::SYMPLIFY,
            SetList::COMMON,
            SetList::COMMENTS,
        ],
    );

    // Strictness
    $ecsConfig->rules([
        DeclareStrictTypesFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
    ]);

    // Code Style
    $ecsConfig->rules([
        LowercaseKeywordsFixer::class,
        MagicConstantCasingFixer::class,
        OrderedClassElementsFixer::class,
        YodaStyleFixer::class,
        NoUselessElseFixer::class,
        FullyQualifiedStrictTypesFixer::class,
    ]);

    $ecsConfig->rulesWithConfiguration([
        VisibilityRequiredFixer::class => ['elements' => ['property', 'method', 'const']],
    ]);

    // Array Syntax
    $ecsConfig->rulesWithConfiguration([
        ArraySyntaxFixer::class => ['syntax' => 'short'],
    ]);

    // Naming Conventions
    $ecsConfig->rules([
        NoWhitespaceBeforeCommaInArrayFixer::class,
        NoShortBoolCastFixer::class,
    ]);

    // Clean Code
    $ecsConfig->rules([
        NoBlankLinesAfterClassOpeningFixer::class,
        NoSuperfluousElseifFixer::class,
        NoTrailingWhitespaceFixer::class,
        NoWhitespaceInBlankLineFixer::class,
    ]);

    $ecsConfig->rulesWithConfiguration([
        NoExtraBlankLinesFixer::class => ['tokens' => ['curly_brace_block', 'extra']],
    ]);

    // Function and Method
    $ecsConfig->rules([
        MethodArgumentSpaceFixer::class,
        NoSpacesAfterFunctionNameFixer::class,
    ]);

    $ecsConfig->rulesWithConfiguration([
        ReturnTypeDeclarationFixer::class => ['space_before' => 'none'],
    ]);

    // Miscellaneous
    $ecsConfig->rules([
        PhpdocAddMissingParamAnnotationFixer::class,
        PhpdocOrderFixer::class,
        PhpdocSummaryFixer::class,
        PhpdocTrimFixer::class,
        PhpdocNoEmptyReturnFixer::class,
    ]);

    $ecsConfig->rulesWithConfiguration([
        BinaryOperatorSpacesFixer::class => ['default' => 'single_space'],
    ]);




    $ecsConfig->rules([
        NoUnusedImportsFixer::class,
        NoAliasFunctionsFixer::class,
        SingleQuoteFixer::class,
    ]);

    $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
        'elements' => [
            'arrays',
            'parameters',
        ],
    ]);
    $ecsConfig->ruleWithConfiguration(
        ClassAttributesSeparationFixer::class,
        [
            'elements' => [
                'const' => 'one',
                'property' => 'one',
                'method' => 'one',
            ],
        ],
    );

    $ecsConfig->ruleWithConfiguration(
        ConcatSpaceFixer::class,
        [
            'spacing' => 'one',
        ],
    );

    $ecsConfig->ruleWithConfiguration(
        CastSpacesFixer::class,
        [
            'space' => 'none',
        ],
    );
};
