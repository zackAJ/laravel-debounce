<?php

namespace Zackaj\LaravelDebounce\Trackers;

enum Driver: string
{
    case CACHE = CacheTracker::class;
}
