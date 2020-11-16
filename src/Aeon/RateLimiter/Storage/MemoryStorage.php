<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Storage;

use Aeon\Calendar\Gregorian\Calendar;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Hit;
use Aeon\RateLimiter\Hits;
use Aeon\RateLimiter\Storage;

final class MemoryStorage implements Storage
{
    private Calendar $calendar;

    /**
     * @var array<string, array<Hit>>
     */
    private array $hits;

    public function __construct(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $this->hits = [];
    }

    public function addHit(string $id, TimeUnit $ttl) : void
    {
        if (!$this->hasFor($id)) {
            $this->hits[$this->normalize($id)] = [];
        }

        $this->hits[$this->normalize($id)][] = new Hit($id, $this->calendar->now(), $ttl);
    }

    public function all(string $id) : Hits
    {
        return new Hits(...$this->hits($id));
    }

    public function count(string $id) : int
    {
        return \count($this->hits($id));
    }

    /**
     * @return array<Hit>
     */
    private function hits(string $id) : array
    {
        if (!$this->hasFor($id)) {
            return [];
        }

        $hits = [];
        /** @var Hit $hit */
        foreach ($this->hits[$this->normalize($id)] as $i => $hit) {
            if (!$hit->expired($this->calendar)) {
                $hits[] = $hit;
            } else {
                unset($this->hits[$this->normalize($id)][$i]);
            }
        }

        $this->hits[$this->normalize($id)] = \array_values($this->hits[$this->normalize($id)]);

        return $hits;
    }

    private function hasFor(string $id) : bool
    {
        if (!\array_key_exists($this->normalize($id), $this->hits)) {
            return false;
        }

        return true;
    }

    private function normalize(string $id) : string
    {
        return \mb_strtolower($id);
    }
}
