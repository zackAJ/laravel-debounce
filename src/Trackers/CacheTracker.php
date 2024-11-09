<?php

namespace Zackaj\LaravelDebounce\Trackers;

use Illuminate\Support\Facades\Cache;
use Zackaj\LaravelDebounce\Contracts\Trackable;

class CacheTracker implements Trackable
{
    public function getReport(string $trackerKey): ?Report
    {
        return Cache::get($trackerKey);
    }

    public function forgetReport(string $trackerKey): ?Report
    {
        $report = $this->getReport($trackerKey);
        Cache::forget($trackerKey);

        return $report;
    }

    public function registerOccurrence(string $trackerKey, Occurrence $occurrence): ?Report
    {
        $report = $this->getReport($trackerKey) ?? app(Report::class);
        $report->occurrences->push($occurrence);
        Cache::set($trackerKey, $report);

        return $report;
    }
}
