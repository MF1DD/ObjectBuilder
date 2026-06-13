<?php

declare(strict_types=1);

namespace MF1DD\Tests\Application\Services;

use DateInterval;
use DatePeriod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use MF1DD\Domain\StockClassHandlerInterface;
use MF1DD\UserInterface\ObjectBuilder;
use MF1DD\Application\Services\StockClassHandlerService;
use Throwable;

class StockClassHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        StockClassHandlerService::setHandlers([]);
    }

    public function testDateIntervalHandler(): void
    {
        $interval = ObjectBuilder::init(DateInterval::class)->build();
        $this->assertInstanceOf(DateInterval::class, $interval);
    }

    public function testDatePeriodHandler(): void
    {
        $period = ObjectBuilder::init(DatePeriod::class)->build();
        $this->assertInstanceOf(DatePeriod::class, $period);
    }

    public function testCustomHandlerRegistration(): void
    {
        $customHandler = new class implements StockClassHandlerInterface {
            public static int $called = 0;

            public function build(ReflectionClass $class, array $parameters, Throwable $previousException): object
            {
                self::$called++;
                return new DateInterval('P1D');
            }

            public static function supports(ReflectionClass $class): bool
            {
                return $class->getName() === DateInterval::class;
            }
        };

        StockClassHandlerService::register($customHandler);
        $interval = ObjectBuilder::init(DateInterval::class)->build();
        $this->assertInstanceOf(DateInterval::class, $interval);
        $this->assertSame(1, $customHandler::$called);
    }

    public function testHandlerServiceReturnsNullForUnknownClass(): void
    {
        $result = StockClassHandlerService::handle(
            new ReflectionClass(\stdClass::class),
            [],
            new \RuntimeException('test'),
        );
        $this->assertNull($result);
    }
}
