<?php

namespace Zackaj\LaravelDebounce\Contracts;

use Carbon\Carbon;

interface DebounceableCommand
{
    public function getLastActivityTimestamp(): ?Carbon;

    public static function before(): void;

    public static function after(): void;

    public function handle();
}
