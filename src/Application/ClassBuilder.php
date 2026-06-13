<?php

declare(strict_types=1);

namespace MF1DD\Application;

use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Throwable;
use MF1DD\Domain\ClassBuilderInterface;
use MF1DD\Domain\DataTypeInterface;
use MF1DD\Domain\Dto\NoValueSet;
use MF1DD\Domain\Dto\Constraints;
use MF1DD\Domain\Dto\Property;
use MF1DD\Domain\Exceptions\ObjectBuilderDataTypeAndClassNotFoundException;
use MF1DD\Domain\Exceptions\ObjectBuilderWrongClassesGivenException;
use MF1DD\Domain\Exceptions\UnknownOrBadFormatNotDeclaredClassException;
use MF1DD\Application\Services\DataTypeService;
use MF1DD\Application\Services\StockClassHandlerService;
use MF1DD\UserInterface\ObjectBuilder;

class ClassBuilder implements ClassBuilderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $constraints;

    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     * @param array<string, array<string, mixed>> $constraints
     *
     * @return mixed
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): mixed
    {
        $this->parameters = $parameters;
        $this->constraints = $constraints;
        $constructor = $class->getConstructor();
        /**
         * Analysieren was die Klasse hat.
         * - Mit Constructor
         * - Ohne Constructor.
         *
         * Wenn die Klasse ein Constructor hat, muss weiter überprüft werden:
         * - Ist der Constructor privat?
         * - gibt es static methoden
         */

        if ($constructor === null) {
            return $this->handleClassWithoutConstructor($class);
        }

        if ($constructor->isPrivate()) {
            $newInstance = $this->instantiateRandomStaticMethod($class);
            if ($newInstance !== null) {
                return $newInstance;
            }

            throw new ObjectBuilderWrongClassesGivenException(
                sprintf(
                    'Cannot handle class "%s" with private constructor and no static methode.',
                    $class->getShortName()
                )
            );
        }

        try {
            return $class->newInstanceArgs($this->handleClassWithConstructor($constructor));
        } catch (Throwable $exception) {
            if (str_contains($exception->getMessage(), 'must be of type array, string given')) {
                throw new InvalidArgumentException(
                    sprintf(
                        'For Objects you must given an array, not an single value. Message: %s',
                        $exception->getMessage()
                    )
                );
            }
            $newInstance = $this->instantiateRandomStaticMethod($class);
            if ($newInstance === null || $newInstance === false) {
                $handled = StockClassHandlerService::handle($class, $this->parameters, $exception);
                if ($handled !== null) {
                    return $handled;
                }

                return $this->tryExceptionSolver($class, $exception);
            }

            return $newInstance;
        }
    }

    private function generateRandomValue(ReflectionParameter|ReflectionProperty $parameter): mixed
    {
        $propertyType = DataTypeService::getDataTypeFromString((string)$parameter->getType());

        if ($propertyType !== null) {
            $propertyType = $propertyType[array_rand($propertyType)];

            $declaringClass = $parameter->getDeclaringClass();
            $propertyType = match ($propertyType) {
                'self', 'static' => $declaringClass ? $declaringClass->getName() : $propertyType,
                'parent' => $declaringClass && ($parent = $declaringClass->getParentClass())
                    ? $parent->getName()
                    : $propertyType,
                default => $propertyType
            };

            $hasUserValue = array_key_exists($parameter->getName(), $this->parameters)
                && !($this->parameters[$parameter->getName()] instanceof NoValueSet);

            if ($hasUserValue) {
                $userValue = $this->parameters[$parameter->getName()];
                $userType = match (true) {
                    is_object($userValue) => $userValue::class,
                    is_int($userValue) => 'int',
                    is_float($userValue) => 'float',
                    is_bool($userValue) => 'bool',
                    is_string($userValue) => 'string',
                    is_array($userValue) => 'array',
                    default => gettype($userValue),
                };
                $allTypes = DataTypeService::getDataTypeFromString((string)$parameter->getType());

                if ($allTypes !== null && count($allTypes) > 1 && in_array($userType, $allTypes, true)) {
                    $propertyType = $userType;
                }
            }
        }

        if (
            array_key_exists($parameter->getName(), $this->parameters) &&
            $this->parameters[$parameter->getName()] === null
        ) {
            $propertyType = null;
        }

        if (
            $propertyType === '?' &&
            array_key_exists($parameter->getName(), $this->parameters) &&
            is_array($this->parameters[$parameter->getName()])
        ) {
            $allTypes = DataTypeService::getDataTypeFromString((string)$parameter->getType());
            $classType = array_values(array_filter($allTypes ?? [], fn(string $t) => $t !== '?'));
            if (!empty($classType)) {
                $propertyType = $classType[0];
            }
        }

        $defaultValue = $this->getDefaultValue($parameter);

        $constraintOptions = $this->constraints[$parameter->getName()] ?? [];
        $property = new Property(
            name: $parameter->getName(),
            type: $propertyType,
            value: $this->parameters[$parameter->getName()] ?? $defaultValue,
            constraints: !empty($constraintOptions) ? new Constraints($constraintOptions) : null,
        );

        $dataTypeHandler = DataTypeService::getDataTypeBuilder($property->type);

        if ($dataTypeHandler instanceof DataTypeInterface) {
            $dataTypeHandler->setProperty($property);

            return $dataTypeHandler->build();
        }

        if (is_string($property->type) && (class_exists($property->type) || interface_exists($property->type))) {
            if ($property->value instanceof NoValueSet) {
                return ObjectBuilder::init($property->type)->build();
            }

            if (is_array($property->value)) {
                return ObjectBuilder::init($property->type, $property->value)->build();
            }

            if (is_object($property->value)) {
                return $property->value;
            }

            return ObjectBuilder::init($property->type)->build();
        }

        throw new ObjectBuilderDataTypeAndClassNotFoundException(
            sprintf(
                'Property name: "%s" with value: "%s" has unknown datatype: "%s"',
                $property->name,
                $property->value,
                $property->type,
            )
        );
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    private function handleClassWithoutConstructor(ReflectionClass $reflectionClass): object
    {
        $object = $reflectionClass->newInstance();

        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isReadOnly()) {
                continue;
            }

            if ($property->getType() instanceof ReflectionType) {
                $value = $this->generateRandomValue($property);

                if ($value !== false) {
                    $property->setValue($object, $value);
                }
            }
        }

        return $object;
    }

    /**
     * @return array<int, mixed>
     */
    private function handleClassWithConstructor(ReflectionMethod $constructor): array
    {
        $parameterValues = [];

        foreach ($constructor->getParameters() as $parameter) {
            $parameterValues[] = $this->generateRandomValue($parameter);
        }

        return $parameterValues;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    private function instantiateRandomStaticMethod(ReflectionClass $reflectionClass): mixed
    {
        $methods = $reflectionClass->getMethods();
        $staticSelfBuildMethods = array_filter(
            $methods,
            fn (ReflectionMethod $method) =>
                $method->isStatic() && $method->getReturnType() !== null && !$method->getReturnType()
                    ->isBuiltin()
        );

        if (empty($staticSelfBuildMethods)) {
            return null;
        }

        $randomStaticMethode = $staticSelfBuildMethods[array_rand($staticSelfBuildMethods)];

        $parameters = [];
        foreach ($randomStaticMethode->getParameters() as $parameter) {
            $parameters[] = $this->generateRandomValue($parameter);
        }

        try {
            $name = $randomStaticMethode->getName();
            return $reflectionClass->getName()::$name(...$parameters);
        } catch (Throwable) {
            // Static method invocation failed, fall through to tryExceptionSolver
        }

        return null;
    }

    private function getDefaultValue(ReflectionParameter|ReflectionProperty $parameter): mixed
    {
        if ($parameter instanceof ReflectionParameter && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter instanceof ReflectionProperty && $parameter->hasDefaultValue()) {
            return $parameter->getDefaultValue();
        }

        return new NoValueSet();
    }

    /**
     * @param ReflectionClass<object> $class
     * @param Throwable $exception
     */
    private function tryExceptionSolver(ReflectionClass $class, Throwable $exception): object
    {
        $newParameters = [];

        if (preg_match('/Unknown or bad format \((.*)\)/', $exception->getMessage(), $unknown)) {
            $newParameters = match ($class->getName()) {
                DateInterval::class => ['P7D'],
                DatePeriod::class => ['R4/1983-08-04T00:06:00Z/P7D'],
                default => throw new UnknownOrBadFormatNotDeclaredClassException($class, $exception),
            };

            return $class->newInstanceArgs($newParameters);
        }

        if (preg_match('/::__construct\(\) accepts (.*) as arguments/', $exception->getMessage(), $matches)) {
            // Try different parameter combinations from the error message until one works

            $parameterOptions = explode(', or ', $matches[1]);

            do {
                $key = array_rand($parameterOptions);
                $parameters = $parameterOptions[$key];
                unset($parameterOptions[$key]);

                foreach ($this->splitParametersFromExceptionMessage($parameters) as $item) {
                    if (is_array($item)) {
                        $item = $item[array_rand($item)];
                    }

                    $dataTypeHandler = DataTypeService::getDataTypeBuilder($item);
                    $property = new Property(
                        name: null,
                        type: $item,
                        value: $dataTypeHandler?->build() ?? new NoValueSet(),
                    );

                    if ($dataTypeHandler instanceof DataTypeInterface) {
                        $dataTypeHandler->setProperty($property);

                        $newParameters[] = $dataTypeHandler->build();
                        continue;
                    }

                    if (is_string($property->type) && (class_exists($property->type) || interface_exists(
                        $property->type
                    ))) {
                        $newParameters[] = ObjectBuilder::init(
                            $property->type,
                            $property->value instanceof NoValueSet ? [] : $property->value
                        )->build();

                        continue;
                    }

                    throw new ObjectBuilderDataTypeAndClassNotFoundException(
                        sprintf(
                            'Property name: "%s" with value: "%s" has unknown datatype: "%s"',
                            $property->name,
                            $property->value,
                            $property->type,
                        )
                    );
                }

                try {
                    return $class->newInstanceArgs($newParameters);
                } catch (Throwable $exception) {
                    if (preg_match('/Unknown or bad format \((.*)\)/', $exception->getMessage(), $unknown)) {
                        $newParameters = match ($class->getName()) {
                            DateInterval::class => ['P7D'],
                            DatePeriod::class => ['R4/1983-08-04T00:06:00Z/P7D'],
                            default => throw new UnknownOrBadFormatNotDeclaredClassException($class, $exception),
                        };

                        return $class->newInstanceArgs($newParameters);
                    }
                }
            } while (!empty($parameterOptions));
        }

        return $class->newInstanceArgs($newParameters);
    }

    /**
     * @return array<int, string|array<int, mixed>>
     */
    private function splitParametersFromExceptionMessage(string $function): array
    {
        $function = str_replace(' [', ', [', $function);
        $parameters = explode(', ', trim($function, '( )'));
        $result = [];
        $newArray = [];
        foreach ($parameters as $parameter) {
            if ($parameter[0] === '[') {
                $first = substr($parameter, 1);
                if ($first !== '') {
                    $newArray[] = $first;
                }
                continue;
            }

            if (str_ends_with($parameter, ']')) {
                $last = substr($parameter, 0, -1);
                if ($last !== '') {
                    $newArray[] = $last;
                }
                $parameter = $newArray;
                $newArray = [];
            }
            if (!empty($newArray)) {
                $newArray[] = $parameter;
            } else {
                $result[] = $parameter;
            }
        }
        return $result;
    }
}
