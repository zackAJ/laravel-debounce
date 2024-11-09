<?php

namespace Zackaj\LaravelDebounce\Debouncers;

use Carbon\Carbon;
use Zackaj\LaravelDebounce\Contracts\Trackable;
use Zackaj\LaravelDebounce\DebounceCommand;
use Zackaj\LaravelDebounce\DebounceJob;
use Zackaj\LaravelDebounce\DebounceNotification;
use Zackaj\LaravelDebounce\Managers\TrackerManager;
use Zackaj\LaravelDebounce\Trackers\Occurrence;
use Zackaj\LaravelDebounce\Trackers\Report;

/**
 * stores and tracks each occurrence, the current and
 * only default tracker uses cache implementation
 */
abstract class TrackerDebouncer extends BaseDebouncer
{
    public Occurrence $occurrence;

    public int $originalDelay;

    public ?Report $report = null;

    public Trackable $tracker;

    public function __construct($delay)
    {
        $this->tracker = TrackerManager::make();
        $this->originalDelay = $delay;
        $this->occurrence = new Occurrence(
            now(),
            request()->headers,
            request()->ip(),
            request()->ips(),
            request()->user(),
        );

        //register if first occurrence ever
        //if a lock instance already exits
        //not if self dispatched
        if ($this->isLocked() || is_null($this->getReport())) {
            $this->registerOccurrence();
        }
    }

    public function getLatestActivityTimestamp(): ?Carbon
    {
        return $this->getReport()?->occurrences->last()?->happenedAt;
    }

    public function getLatestActivityTimestampFallback(): ?Carbon
    {
        return now()->subSeconds($this->getOriginalDelay());
    }

    final public function beforeExecute(): void
    {
        $this->forgetReport();
        $this->before();
    }

    final public function afterExecute(): void
    {
        $this->after();
    }

    final public function getReport(): ?Report
    {
        return $this
            ->tracker
            ->getReport($this->uniqueKey()) ??
            $this->report;
    }

    //clear timestamp tracker
    private function forgetReport(): void
    {
        $this->report = $this->tracker->forgetReport($this->uniqueKey());
    }

    private function registerOccurrence(): void
    {
        $this->report = $this->tracker->registerOccurrence($this->uniqueKey(), $this->occurrence);
    }

    /**
     * NOTE: I don't like this, but for now it's okay
     *
     * checks if the target extends one of my debounceables
     * determines if the target has implementations
     */
    protected function isDebounceable(mixed $target): bool
    {
        $debounceables = [
            DebounceNotification::class,
            DebounceJob::class,
            DebounceCommand::class,
        ];

        foreach ($debounceables as $debounceable) {
            if ($target instanceof $debounceable) {
                return true;
            }
        }

        return false;
    }

    public function before() {}

    public function after() {}
}
