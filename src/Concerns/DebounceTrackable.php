<?php

namespace Zackaj\LaravelDebounce\Concerns;

use Zackaj\LaravelDebounce\Trackers\Report;

trait DebounceTrackable
{
    protected ?Report $report = null;

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): Report
    {
        return $this->report = $report;
    }
}
