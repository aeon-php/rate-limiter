<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Tests\Unit\Algorithm;

use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\Gregorian\GregorianCalendarStub;
use Aeon\Calendar\Gregorian\TimeZone;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Algorithm\LeakyBucketAlgorithm;
use Aeon\RateLimiter\Exception\RateLimitException;
use Aeon\RateLimiter\Storage\MemoryStorage;
use PHPUnit\Framework\TestCase;

final class LeakyBucketAlgorithmTest extends TestCase
{
    public function test_leaky_bucket_algorithm() : void
    {
        $calendar = new GregorianCalendarStub(TimeZone::UTC());
        $calendar->setNow(DateTime::fromString('2020-01-01 00:00:00 UTC'));

        $algorithm = new LeakyBucketAlgorithm($calendar, $bucketSize = 5, $leakSize = 2, TimeUnit::seconds(10));

        $this->assertSame($bucketSize, $algorithm->capacity('id', $storage = new MemoryStorage($calendar)));

        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);

        $this->assertSame(10, $algorithm->estimate('id', $storage)->inSeconds());
        $this->assertSame(0, $algorithm->capacity('id', $storage));

        $calendar->setNow($calendar->now()->add(TimeUnit::seconds(10)->add(TimeUnit::millisecond())));

        $this->assertSame(0, $algorithm->estimate('id', $storage)->inSeconds());
        $this->assertSame(2, $algorithm->capacity('id', $storage));

        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);

        $this->assertSame('9.999000', $algorithm->estimate('id', $storage)->inSecondsPrecise());
    }

    public function test_leaky_bucket_algorithm_with_too_many_hits() : void
    {
        $calendar = new GregorianCalendarStub(TimeZone::UTC());
        $calendar->setNow(DateTime::fromString('2020-01-01 00:00:00 UTC'));

        $algorithm = new LeakyBucketAlgorithm($calendar, $bucketSize = 5, $leakSize = 2, TimeUnit::seconds(10));

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Execution "id" was limited for the next 10.000000 seconds');

        $algorithm->hit('id', $storage = new MemoryStorage($calendar));
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
        $algorithm->hit('id', $storage);
    }
}
