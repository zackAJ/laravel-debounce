<?php

namespace Zackaj\LaravelDebounce;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Notifications\Notification;
use Zackaj\LaravelDebounce\Concerns\DebounceTrackable;
use Zackaj\LaravelDebounce\Contracts\DebounceableNotification;
use Zackaj\LaravelDebounce\Facades\Debounce;

abstract class DebounceNotification extends Notification implements DebounceableNotification
{
    use DebounceTrackable;

    public function before($notifiables): void {}

    public function after($notifiables): void {}

    /**
     * @param  \Illuminate\Support\Collection|array|mixed  $notifiables
     */
    public function getLatestActivityTimestamp(mixed $notifiables): ?Carbon
    {
        return null;
    }

    /**
     * @param  \Illuminate\Support\Collection|array|mixed  $notifiables
     */
    public function debounce(mixed $notifiables, int $delay, string $uniqueKey, bool $sendNow = false): PendingDispatch
    {
        return Debounce::notification($notifiables, $this, $delay, $uniqueKey, $sendNow);
    }
}
