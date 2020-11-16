<?php

declare(strict_types=1);

namespace Aeon\RateLimiter;

/**
 * @psalm-immutable
 */
final class Hits implements \Countable
{
    /**
     * @var array<Hit>
     */
    private array $hits;

    public function __construct(Hit ...$hits)
    {
        $this->hits = $hits;
    }

    public function count() : int
    {
        return \count($this->hits);
    }

    public function oldest() : ?Hit
    {
        $oldest = null;

        foreach ($this->hits as $hit) {
            if ($oldest === null) {
                $oldest = $hit;

                continue;
            }

            if ($hit->isOlderThan($oldest)) {
                $oldest = $hit;
            }
        }

        return $oldest;
    }
}
