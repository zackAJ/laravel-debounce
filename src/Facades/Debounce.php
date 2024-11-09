<?php

namespace Zackaj\LaravelDebounce\Facades;

use Illuminate\Support\Facades\Facade;
use Zackaj\LaravelDebounce\Debouncer;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch notification($notifiables, Notification $notification, int $delay, string $uniqueKey, bool $sendNow = false)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch job( mixed $job, int $delay, string $uniqueKey, bool $sync = false)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|int command( string $command, int $delay, string $uniqueKey, array $parameters = [], bool $toQueue = false, \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer = null)
 *
 *  @see \Zackaj\LaravelDebounce\Helpers\Debouncer;
 */
class Debounce extends Facade
{
    /*
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Debouncer::class;
    }
}
