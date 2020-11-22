<?php

declare(strict_types=1);

namespace Aeon\RateLimiter\Tests\Unit;

use Aeon\Calendar\System\Process;
use Aeon\Calendar\TimeUnit;
use Aeon\RateLimiter\Algorithm;
use Aeon\RateLimiter\Exception\RateLimitException;
use Aeon\RateLimiter\RateLimiter;
use Aeon\RateLimiter\Storage;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    public function test_hit_method_throwing_exception() : void
    {
        $algorithm = $this->createStub(Algorithm::class);
        $algorithm->method('hit')->willThrowException(new RateLimitException('id', TimeUnit::seconds(10)));

        $rateLimiter = new RateLimiter(
            $algorithm,
            $this->createMock(Storage::class)
        );

        $this->expectExceptionMessage(RateLimitException::class);
        $this->expectExceptionMessage('Execution "id" was limited for the next 10.000000 seconds');

        $rateLimiter->hit('id');
    }

    public function test_estimate_method_throwing_exception() : void
    {
        $algorithm = $this->createStub(Algorithm::class);
        $algorithm->method('estimate')->willReturn(TimeUnit::second());

        $rateLimiter = new RateLimiter(
            $algorithm,
            $this->createMock(Storage::class)
        );

        $this->assertSame(1, $rateLimiter->estimate('id')->inSeconds());
    }

    public function test_throttle_without_waiting() : void
    {
        $algorithm = $this->createStub(Algorithm::class);

        $rateLimiter = new RateLimiter(
            $algorithm,
            $this->createMock(Storage::class)
        );

        $process = $this->createMock(Process::class);
        $process->expects($this->never())->method('sleep');

        $rateLimiter->throttle('id', $process);
    }

    public function test_capacity() : void
    {
        $algorithm = $this->createStub(Algorithm::class);
        $algorithm->method('capacity')->willReturn(10);

        $rateLimiter = new RateLimiter(
            $algorithm,
            $this->createMock(Storage::class)
        );

        $this->assertSame(10, $rateLimiter->capacity('id'));
    }

    public function test_throttle_and_wait() : void
    {
        $algorithm = $this->createMock(Algorithm::class);
        $algorithm->expects($this->exactly(2))
            ->method('hit')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new RateLimitException('id', $sleepTime = TimeUnit::seconds(10))),
                null
            );

        $rateLimiter = new RateLimiter(
            $algorithm,
            $this->createMock(Storage::class)
        );

        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('sleep')->with($sleepTime);

        $rateLimiter->throttle('id', $process);
    }
}
