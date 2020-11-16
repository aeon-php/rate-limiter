<?php

declare(strict_types=1);

namespace Aeon\RateLimiter;

use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Exception\RateLimitException;

interface Algorithm
{
    /**
     * @throws RateLimitException
     */
    public function hit(string $id, Storage $storage) : void;

    /**
     * Estimate the time in which next hit is allowed.
     */
    public function nextHit(string $id, Storage $storage) : TimeUnit;
}
