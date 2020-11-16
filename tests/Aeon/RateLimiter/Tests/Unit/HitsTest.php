<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Tests\Unit;

use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Hit;
use Aeon\RateLimiter\Hits;
use PHPUnit\Framework\TestCase;

final class HitsTest extends TestCase
{
    public function test_count() : void
    {
        $hits = new Hits(
            new Hit('id', DateTime::fromString('2020-01-01 00:00:00 UTC'), TimeUnit::minute()),
            new Hit('id', DateTime::fromString('2020-01-01 00:00:00 UTC'), TimeUnit::minute()),
        );

        $this->assertSame($hits->count(), 2);
    }

    public function test_oldest_hit() : void
    {
        $hits = new Hits(
            $hit1 = new Hit('id', DateTime::fromString('2020-01-01 03:00:00 UTC'), TimeUnit::minute()),
            $hit2 = new Hit('id', DateTime::fromString('2020-01-01 00:00:00 UTC'), TimeUnit::minute()),
            $hit3 = new Hit('id', DateTime::fromString('2020-01-01 01:00:00 UTC'), TimeUnit::minute()),
        );

        $this->assertSame($hits->oldest(), $hit2);
    }
}
