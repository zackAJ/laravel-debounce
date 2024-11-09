<?php

namespace Zackaj\LaravelDebounce\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;

interface DebounceableNotification extends ShouldQueue
{
    /**
     * the timestamp that should be compared to the debounce interval
     *
     * @param  \Illuminate\Support\Collection|array|mixed  $notifiables
     */
    public function getLatestActivityTimestamp(mixed $notifiables): ?Carbon;

    public function after($notifiables): void;

    public function before($notifiables): void;

    public function debounce($notifiables, int $delay, string $uniqueKey): PendingDispatch;
}
