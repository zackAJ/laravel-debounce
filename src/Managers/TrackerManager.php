<?php

namespace Zackaj\LaravelDebounce\Managers;

use Zackaj\LaravelDebounce\Contracts\Trackable;
use Zackaj\LaravelDebounce\Trackers\Driver;

class TrackerManager
{
    public function __construct(public Trackable $tracker) {}

    public static function make(): Trackable
    {
        return static::driver(static::getDefaultDriver());
    }

    public static function driver(Driver $trackerDriver): Trackable
    {
        return (new self(new $trackerDriver->value))->tracker;
    }

    public static function getDefaultDriver(): Driver
    {
        $debounceDriver = match (config('debounce.driver')) {
            'cache' => Driver::CACHE,
            default => Driver::CACHE,
        };

        return $debounceDriver;
    }
}
