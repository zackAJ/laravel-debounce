<?php

namespace Zackaj\LaravelDebounce\Contracts;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\PendingDispatch;

interface DebounceableJob
{
    public function getLatestActivityTimestamp(): ?Carbon;

    public function after(): void;

    public function before(): void;

    public function debounce(int $delay, string $uniqueKey, bool $sync = false): PendingDispatch;

    public function handle(): void;
}
