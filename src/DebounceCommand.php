<?php

namespace Zackaj\LaravelDebounce;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Context;
use Symfony\Component\Console\Output\OutputInterface;
use Zackaj\LaravelDebounce\Contracts\DebounceableCommand;
use Zackaj\LaravelDebounce\Facades\Debounce;
use Zackaj\LaravelDebounce\Trackers\Report;

abstract class DebounceCommand extends Command implements DebounceableCommand
{
    public function getLatestActivityTimestamp(): ?Carbon
    {
        return null;
    }

    public static function before(): void {}

    public static function after(): void {}

    public function debounce(int $delay, string $uniqueKey, array $parameters = [], bool $toQueue = false, ?OutputInterface $outputBuffer = null): PendingDispatch
    {
        return Debounce::command(
            $this->getCommandSignature(),
            $delay,
            $uniqueKey,
            $parameters,
            $toQueue,
            $outputBuffer
        );
    }

    /**
     * get the command signature without parameters
     */
    public function getCommandSignature(): string
    {
        return collect(Artisan::all())->where(fn ($command) => $command::class === static::class)
            ->keys()
            ->firstOrFail();
    }

    final public function getReport(): Report
    {
        return Context::getHidden('report');
    }
}
