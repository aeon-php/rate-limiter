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
    public function estimate(string $id, Storage $storage) : TimeUnit;

    /**
     * Return hits left before throttling next hit.
     */
    public function capacity(string $id, Storage $storage) : int;
}
