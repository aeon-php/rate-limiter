<?php

declare(strict_types=1);

namespace Aeon\RateLimiter;

use Aeon\Calendar\System\Process;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Exception\RateLimitException;

final class RateLimiter
{
    private Algorithm $algorithm;

    private Storage $storage;

    public function __construct(Algorithm $algorithm, Storage $storage)
    {
        $this->algorithm = $algorithm;
        $this->storage = $storage;
    }

    /**
     * @throws RateLimitException
     */
    public function hit(string $id) : void
    {
        $this->algorithm->hit($id, $this->storage);
    }

    public function estimate(string $id) : TimeUnit
    {
        return $this->algorithm->nextHit($id, $this->storage);
    }

    public function throttle(string $id, Process $process) : void
    {
        try {
            $this->algorithm->hit($id, $this->storage);
        } catch (RateLimitException $rateLimitException) {
            $process->sleep($rateLimitException->cooldown());
            $this->algorithm->hit($id, $this->storage);
        }
    }
}
