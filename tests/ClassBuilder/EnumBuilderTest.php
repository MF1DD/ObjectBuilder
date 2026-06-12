<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Timelesstron\ObjectBuilder\ClassBuilder\EnumBuilder;
use Timelesstron\ObjectBuilder\ObjectBuilder;
use Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\MyTestEnumeration;

class EnumBuilderTest extends TestCase
{
    public function testEnumBuilderWithoutParameters(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertContains(
                (new EnumBuilder())->build(
                    new ReflectionClass(MyTestEnumeration::class),
                    []
                ),
                MyTestEnumeration::cases()
            );
        }
    }

    public function testEnumBuilderWithOneParameter(): void
    {
        $this->assertSame(
            MyTestEnumeration::OK,
            (new EnumBuilder())->build(
                new ReflectionClass(MyTestEnumeration::class),
                ['OK']
            )
        );
    }

    public function testEnumBuilderWithMultipleParameters(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertContains(
                (new EnumBuilder())->build(
                    new ReflectionClass(MyTestEnumeration::class),
                    ['WARNING', 'ERROR']
                ),
                [MyTestEnumeration::WARNING, MyTestEnumeration::ERROR]
            );
        }
    }

    public function testEnumBuilderThrowAnExceptionByGivenAWrongParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid parameter given vor enum MyTestEnumeration: "NOT_EXIST_KEY".'
        );

        (new EnumBuilder())->build(
            new ReflectionClass(MyTestEnumeration::class),
            ['NOT_EXIST_KEY']
        );
    }
}
