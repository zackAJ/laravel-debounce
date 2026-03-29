<?php

namespace Zackaj\LaravelDebounce\Trackers;

use Illuminate\Support\Collection;

class Report
{
    /**
     * @param  Collection<int, Occurrence>  $occurrences
     */
    public function __construct(public Collection $occurrences) {}
}
