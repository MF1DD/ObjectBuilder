<?php

declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\ClassBuilder\Interface;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt;
use PhpParser\Node\UnionType;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use RuntimeException;
use Timelesstron\ObjectBuilder\ClassBuilder\Dto\NoValueSet;
use Timelesstron\ObjectBuilder\ClassBuilder\InterfaceBuilder;
use Timelesstron\ObjectBuilder\Dto\Property;
use Timelesstron\ObjectBuilder\Exceptions\InfinityInterfaceException;
use Timelesstron\ObjectBuilder\Exceptions\ObjectBuilderUnknownClassTypeGivenException;
use Timelesstron\ObjectBuilder\ObjectBuilder;
use Timelesstron\ObjectBuilder\Services\DataTypeService;

final class FileContentHandler implements HandlerInterface
{
    private ReflectionClass $reflectionClass;

    private array $parameters;

    private string $className;

    private string $namespace = '';

    public function execute(ReflectionClass $reflectionClass, array $parameters): object
    {
        $this->reflectionClass = $reflectionClass;
        $this->parameters = $parameters;
        $this->className = $this->increaseClassNameIfNeeded();

        $fileName = $reflectionClass->getFileName() ?: '';
        if (!$fileName || !file_exists($fileName)) {
            throw new ObjectBuilderUnknownClassTypeGivenException();
        }

        $sourceCode = file_get_contents($fileName);
        if ($sourceCode === false || trim($sourceCode) === '') {
            throw new ObjectBuilderUnknownClassTypeGivenException();
        }

        $classCode = $this->transformInterfaceToClass($sourceCode);

        $tmpFile = tempnam(sys_get_temp_dir(), 'obuild_interface_');
        if ($tmpFile === false) {
            throw new RuntimeException('Failed to create temporary file for interface builder.');
        }

        file_put_contents($tmpFile, $classCode);

        try {
            include $tmpFile;
        } finally {
            unlink($tmpFile);
        }

        $cn = $this->className;
        if ($this->namespace) {
            $cn = $this->namespace . '\\' . $cn;
        }

        return new $cn();
    }

    public static function support(ReflectionClass $reflectionClass): bool
    {
        if (!$reflectionClass->getFileName()) {
            return false;
        }

        return !empty(
            file($reflectionClass->getFileName(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        );
    }

    private function transformInterfaceToClass(string $sourceCode): string
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($sourceCode);
        if ($ast === null) {
            throw new ObjectBuilderUnknownClassTypeGivenException();
        }

        $className = $this->className;
        $namespaceRef = new \stdClass();
        $namespaceRef->value = '';
        $parameters = $this->parameters;
        $reflectionClass = $this->reflectionClass;

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new class(
            $className,
            $namespaceRef,
            $parameters,
            $reflectionClass,
        ) extends NodeVisitorAbstract {

            public function __construct(
                private readonly string $className,
                private readonly \stdClass $namespaceRef,
                private readonly array $parameters,
                private readonly ReflectionClass $reflectionClass,
            ) {
            }

            public function enterNode(Node $node): null|array|Node
            {
                if ($node instanceof Stmt\Namespace_) {
                    $this->namespaceRef->value = $node->name ? $node->name->toString() : '';
                }

                return null;
            }

            public function leaveNode(Node $node): null|array|Node
            {
                if ($node instanceof Stmt\Namespace_) {
                    $this->namespaceRef->value = $node->name ? $node->name->toString() : '';
                }

                if ($node instanceof Stmt\Interface_) {
                    $newMethods = [];
                    foreach ($node->getMethods() as $method) {
                        $newMethods[] = $this->transformMethod($method);
                    }

                    $nonMethodStmts = [];
                    foreach ($node->stmts as $stmt) {
                        if (!$stmt instanceof Stmt\ClassMethod) {
                            $nonMethodStmts[] = $stmt;
                        }
                    }

                    return new Stmt\Class_(
                        new Identifier($this->className),
                        [
                            'implements' => [new Name\FullyQualified($this->reflectionClass->getName())],
                            'stmts' => array_merge($nonMethodStmts, $newMethods),
                            'comments' => $node->getComments(),
                        ],
                    );
                }

                return null;
            }

            private function transformMethod(Stmt\ClassMethod $method): Stmt\ClassMethod
            {
                $methodName = $method->name->toString();
                $returnType = $method->returnType;

                $bodyStmts = $this->buildMethodBody($methodName, $returnType);

                return new Stmt\ClassMethod(
                    new Identifier($methodName),
                    [
                        'flags' => $method->flags,
                        'params' => $method->params,
                        'returnType' => $returnType,
                        'stmts' => $bodyStmts,
                        'comments' => $method->getComments(),
                    ],
                );
            }

            /**
             * @return array<int, Stmt>
             */
            private function buildMethodBody(string $methodName, null|Identifier|Name|ComplexType $returnType): array
            {
                if ($returnType === null) {
                    return [];
                }

                $returnTypeString = $this->typeNodeToString($returnType);

                if ($returnTypeString === 'void') {
                    return [];
                }

                if ($returnTypeString === 'never') {
                    return [new Stmt\Expression(
                        new Expr\Throw_(new Expr\New_(new Name\FullyQualified(\RuntimeException::class), [
                            new Node\Arg(new Node\Scalar\String_('Method should never be called')),
                        ]))
                    )];
                }

                $buildResultString = $this->buildReturnExpression($methodName, $returnTypeString);

                $returnNode = $this->parseExpression($buildResultString);

                return [new Stmt\Return_($returnNode)];
            }

            private function typeNodeToString(null|Identifier|Name|ComplexType $type): string
            {
                if ($type === null) {
                    return '';
                }

                if ($type instanceof Identifier) {
                    return $type->toString();
                }

                if ($type instanceof Name\FullyQualified) {
                    return '\\' . $type->toString();
                }

                if ($type instanceof Name) {
                    return $type->toString();
                }

                if ($type instanceof NullableType) {
                    return '?' . $this->typeNodeToString($type->type);
                }

                if ($type instanceof UnionType) {
                    $parts = [];
                    foreach ($type->types as $t) {
                        $parts[] = $this->typeNodeToString($t);
                    }
                    return implode('|', $parts);
                }

                if ($type instanceof Node\IntersectionType) {
                    $parts = [];
                    foreach ($type->types as $t) {
                        $parts[] = $this->typeNodeToString($t);
                    }
                    return implode('&', $parts);
                }

                return '';
            }

            private function buildReturnExpression(string $methodName, string $returnTypeString): string
            {
                $returnTypes = DataTypeService::getDataTypeFromString($returnTypeString);
                $resolvedType = $returnTypes[array_rand($returnTypes)];

                $property = new Property(
                    name: $methodName,
                    type: $resolvedType,
                    value: $this->parameters[$methodName] ?? new NoValueSet(),
                );

                $dataTypeBuilder = DataTypeService::getDataTypeBuilder($resolvedType);

                if ($dataTypeBuilder !== null) {
                    if (!$property->value instanceof NoValueSet) {
                        return $dataTypeBuilder->setProperty($property)->buildAsString();
                    }

                    return $dataTypeBuilder->buildAsString();
                }

                if (!$property->value instanceof NoValueSet) {
                    return $this->buildObjectReturnExpression($resolvedType, $property->value);
                }

                return $this->buildObjectReturnExpression($resolvedType, null);
            }

            private function buildObjectReturnExpression(string $type, mixed $value): string
            {
                $namespace = $this->namespaceRef->value;
                $nsPrefix = $namespace ? '\\' : '';

                if ($value !== null && !$value instanceof NoValueSet) {
                    if (is_object($value)) {
                        return sprintf(
                            'unserialize(\'%s\')',
                            serialize($value)
                        );
                    }

                    if (is_array($value)) {
                        return sprintf(
                            '%s%s::init(%s::class, %s)->build()',
                            $nsPrefix,
                            ObjectBuilder::class,
                            $type,
                            var_export($value, true)
                        );
                    }

                    return var_export($value, true);
                }

                if (str_contains($this->className, $type . 'Class')) {
                    $returnTypeWithNamespace = sprintf(
                        '%s\\%s',
                        trim($namespace),
                        $type
                    );

                    if (InterfaceBuilder::counter() > InterfaceBuilder::MAX_ALLOWED_INFINITY_INTERFACE_LOADER) {
                        throw new InfinityInterfaceException();
                    }

                    if (class_exists($returnTypeWithNamespace) || interface_exists($returnTypeWithNamespace)) {
                        return sprintf(
                            'unserialize(\'%s\')',
                            serialize(ObjectBuilder::init($returnTypeWithNamespace)->build())
                        );
                    }

                    throw new ObjectBuilderUnknownClassTypeGivenException();
                }

                return sprintf(
                    '%s%s::init(%s::class)->build()',
                    $nsPrefix,
                    ObjectBuilder::class,
                    $type
                );
            }

            private function parseExpression(string $expression): Expr
            {
                static $parser = null;
                if ($parser === null) {
                    $parser = (new ParserFactory())->createForNewestSupportedVersion();
                }

                $stmts = $parser->parse("<?php {$expression};");
                if ($stmts === null || empty($stmts)) {
                    return new Node\Scalar\String_($expression);
                }

                $first = $stmts[0];
                if ($first instanceof Stmt\Expression && $first->expr instanceof Expr) {
                    return $first->expr;
                }

                return new Node\Scalar\String_($expression);
            }
        });

        $traverser->traverse($ast);

        $this->namespace = $namespaceRef->value;

        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($ast);
    }

    public function getReturnType(?string $dataType): string
    {
        $returnTypes = DataTypeService::getDataTypeFromString($dataType);

        return $returnTypes[array_rand($returnTypes)];
    }

    private function increaseClassNameIfNeeded(): string
    {
        $baseName = sprintf('%sClass', $this->reflectionClass->getShortName());
        $suffix = bin2hex(random_bytes(4));

        return $baseName . $suffix;
    }
}
