<?php

declare(strict_types=1);

namespace Aeon\RateLimiter;

use Aeon\Calendar\Gregorian\Calendar;
use Aeon\Calendar\Gregorian\DateTime;
use Aeon\Calendar\TimeUnit;

/**
 * @psalm-immutable
 */
final class Hit
{
    private string $id;

    private DateTime $dateTime;

    private TimeUnit $ttl;

    public function __construct(string $id, DateTime $dateTime, TimeUnit $ttl)
    {
        $this->id = $id;
        $this->dateTime = $dateTime;
        $this->ttl = $ttl;
    }

    public function expired(Calendar $calendar) : bool
    {
        return !$this->dateTime->add($this->ttl)->isAfterOrEqual($calendar->now());
    }

    public function isOlderThan(self $hit) : bool
    {
        return $this->dateTime->isBefore($hit->dateTime);
    }

    public function ttlLeft(Calendar $calendar) : TimeUnit
    {
        $ttlLeft = $calendar->now()->until($this->dateTime->add($this->ttl))->distance();

        return $ttlLeft->isPositive() ? $ttlLeft : TimeUnit::seconds(0);
    }
}
