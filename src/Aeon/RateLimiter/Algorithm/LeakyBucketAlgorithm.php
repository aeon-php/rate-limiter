<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Algorithm;

use Aeon\Calendar\Gregorian\Calendar;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Algorithm;
use Aeon\RateLimiter\Exception\RateLimitException;
use Aeon\RateLimiter\Storage;

final class LeakyBucketAlgorithm implements Algorithm
{
    private Calendar $calendar;

    private int $bucketSize;

    private int $leakSize;

    private TimeUnit $leakTime;

    public function __construct(Calendar $calendar, int $bucketSize, int $leakSize, TimeUnit $leakTime)
    {
        $this->calendar = $calendar;
        $this->bucketSize = $bucketSize;
        $this->leakSize = $leakSize;
        $this->leakTime = $leakTime;
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function hit(string $id, Storage $storage) : void
    {
        $hits = $storage->all($id);

        if ($hits->count() >= $this->bucketSize) {
            /** @phpstan-ignore-next-line */
            throw new RateLimitException($id, $hits->oldest()->ttlLeft($this->calendar));
        }

        $ttl = TimeUnit::seconds((int) (\floor($hits->count() / $this->leakSize) * $this->leakTime->inSeconds() + $this->leakTime->inSeconds()));

        $storage->addHit($id, $ttl);
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function nextHit(string $id, Storage $storage) : TimeUnit
    {
        $hits = $storage->all($id);

        if ($hits->count() >= $this->bucketSize) {
            /** @phpstan-ignore-next-line */
            return $hits->oldest()->ttlLeft($this->calendar);
        }

        return TimeUnit::seconds(0);
    }
}
