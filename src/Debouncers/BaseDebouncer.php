<?php

namespace Zackaj\LaravelDebounce\Debouncers;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use ReflectionProperty;
use Zackaj\LaravelDebounce\Contracts\Debounceable;

abstract class BaseDebouncer implements Debounceable, ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected ?Carbon $lastActivityTimesStamp = null;

    /**
     * Handle the debouncer logic, main lifecycle
     */
    final public function handle(): void
    {
        if (is_null($this->getTimestamp())) {
            return;
        }
        if ($this->withinInterval()) {
            $this::dispatch(...$this->getConstructorArgs())
                ->delay(
                    $this
                        ->getTimestamp()
                        ->copy()
                        ->addSeconds($this->getOriginalDelay())
                );

            return;
        }

        $this->beforeExecute();
        $this->execute();
        $this->afterExecute();
    }

    /**
     * check if activity is within the debounce interval
     */
    final protected function withinInterval(): bool
    {
        if (is_null($this->getTimestamp())) {
            return false;
        }

        return $this->getTimestamp()->diffInSeconds(now()) <= $this->getOriginalDelay();
    }

    /**
     * get the constructor arguments for the instance
     * used to dispatch another instance recursively
     */
    final public function getConstructorArgs(): array
    {
        $reflection = new \ReflectionClass($this);
        $constructor_parameters = $reflection->getConstructor()->getParameters();
        $constructor_args = array_reduce($constructor_parameters, function ($accumilator, $parameter) {
            if ($parameter->name === 'delay') {
                array_push($accumilator, $this->getOriginalDelay());
            } else {
                array_push($accumilator, $this->{$parameter->name});
            }

            return $accumilator;
        }, []);

        return $constructor_args;
    }

    /*
    * get the default original delay, needed because the delay of
    * new dispatched job can be a Carbon instance
    * this needs to be in seconds
    */
    public function getOriginalDelay(): int
    {
        $defaultDelay = (new ReflectionProperty($this::class, 'delay'))
            ->getDefaultValue();

        return $defaultDelay;
    }

    // source of truth of the last activity registered
    final public function getTimestamp(): ?Carbon
    {
        return $this->lastActivityTimesStamp ??=
            $this->getLastActivityTimestamp() ??
            $this->getLastActivityTimestampFallback();
    }

    final protected function uniqueKey(): string
    {
        return $this::class.':'.$this->uniqueId();
    }

    // determine if debouncer is locked already, no param is required
    final public function isLocked(): bool
    {
        $lock = $this->getLock($this->getLockKey());
        $acquired = $lock->get();
        $lock->release();

        return ! $acquired;
    }

    // get the lock key of the debouncer, no param is required
    final protected function getLockKey(?string $uniqueKey = null): string
    {
        if (is_null($uniqueKey)) {
            $uniqueKey = $this->uniqueId();
        }
        $laravelPrefix = 'laravel_unique_job:'.get_class($this).':'; // The Laravel way of doing it

        return $laravelPrefix.$uniqueKey;
    }

    // get a lock
    final protected function getLock(string $key, int $seconds = 10): Lock
    {
        return Cache::lock($key, $seconds);
    }

    public function beforeExecute(): void {}

    public function afterExecute(): void {}
}
