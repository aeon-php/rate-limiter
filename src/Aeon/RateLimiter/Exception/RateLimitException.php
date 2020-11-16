<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Exception;

use Aeon\Calendar\TimeUnit;

final class RateLimitException extends RuntimeException
{
    private string $id;

    private TimeUnit $cooldown;

    public function __construct(string $id, TimeUnit $cooldown, \Throwable $previous = null)
    {
        parent::__construct("Execution \"{$id}\" was limited for the next " . $cooldown->inSecondsPrecise() . ' seconds', 0, $previous);

        $this->id = $id;
        $this->cooldown = $cooldown;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function cooldown() : TimeUnit
    {
        return $this->cooldown;
    }
}
