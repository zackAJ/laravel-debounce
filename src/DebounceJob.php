<?php

namespace Zackaj\LaravelDebounce;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\PendingDispatch;
use Zackaj\LaravelDebounce\Concerns\DebounceTrackable;
use Zackaj\LaravelDebounce\Contracts\DebounceableJob;
use Zackaj\LaravelDebounce\Facades\Debounce;

abstract class DebounceJob implements DebounceableJob
{
    use DebounceTrackable;

    public function getLatestActivityTimestamp(): ?Carbon
    {
        return null;
    }

    public function after(): void {}

    public function before(): void {}

    public function debounce(int $delay, string $uniqueKey, bool $sync = false): PendingDispatch
    {
        return Debounce::job($this, $delay, $uniqueKey, $sync);
    }
}
