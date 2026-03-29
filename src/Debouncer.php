<?php

namespace Zackaj\LaravelDebounce;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Symfony\Component\Console\Output\OutputInterface;
use Zackaj\LaravelDebounce\Debouncers\CommandDebouncer;
use Zackaj\LaravelDebounce\Debouncers\JobDebouncer;
use Zackaj\LaravelDebounce\Debouncers\NotificationDebouncer;
use Zackaj\LaravelDebounce\Jobs\DebounceJob;

class Debouncer
{
    /**
     * @param  Collection|array|mixed  $notifiables
     */
    public function notification(mixed $notifiables, Notification|DebounceNotification $notification, int $delay, string $uniqueKey, bool $sendNow = false): ?PendingDispatch
    {
        if (config('debounce.enabled') === false) {
            $sendNow ?
               FacadesNotification::sendNow($notifiables, $notification) :
               FacadesNotification::send($notifiables, $notification);

            return null;
        }

        $uniqueKey = $notification::class.'-'.$uniqueKey;

        return NotificationDebouncer::dispatch($notifiables, $notification, $delay, $uniqueKey, $sendNow);
    }

    /**
     * @param  DebounceJob|mixin  $job
     */
    public function job($job, int $delay, string $uniqueKey, bool $sync = false): ?PendingDispatch
    {
        if (config('debounce.enabled') === false) {
            $sync ? dispatch_sync($job) : dispatch($job);

            return null;
        }

        $uniqueKey = $job::class.'-'.$uniqueKey;

        return JobDebouncer::dispatch($job, $delay, $uniqueKey, $sync);
    }

    /**
     * @param  array<string,string>  $parameters
     */
    public function command(
        string $command,
        int $delay,
        string $uniqueKey,
        array $parameters = [],
        bool $toQueue = false,
        ?OutputInterface $outputBuffer = null
    ): PendingDispatch|int {

        if (config('debounce.enabled') === false) {
            return $toQueue ?
                Artisan::queue($command, $parameters) :
                Artisan::call($command, $parameters, $outputBuffer);
        }

        $commandClass = Artisan::all()[$command]::class;
        $uniqueKey = $commandClass.'-'.$uniqueKey;

        return CommandDebouncer::dispatch($command, $parameters, (int) $delay, $uniqueKey, $toQueue, $outputBuffer);

    }
}
