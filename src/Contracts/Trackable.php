<?php

namespace Zackaj\LaravelDebounce\Contracts;

use Zackaj\LaravelDebounce\Trackers\Occurrence;
use Zackaj\LaravelDebounce\Trackers\Report;

interface Trackable
{
    public function registerOccurrence(string $trackerKey, Occurrence $occurrence): ?Report;

    public function getReport(string $trackerKey): ?Report;

    public function forgetReport(string $trackerKey): ?Report;
}
