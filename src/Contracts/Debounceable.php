<?php

namespace Zackaj\LaravelDebounce\Contracts;

use Carbon\Carbon;

interface Debounceable
{
    /**
     * used for redis lock to prevent dispatching another job
     * if one already exists it won't be added to queue
     * implemented by ShouldBeUniqueUntilProcessing
     */
    public function uniqueId(): string;

    public function beforeExecute(): void;

    /**
     * handle logic when debounce is finally fired
     */
    public function execute(): void;

    public function afterExecute(): void;

    public function getTimestamp(): ?Carbon;

    public function getLastActivityTimestamp(): ?Carbon;

    public function getLastActivityTimestampFallback(): ?Carbon;

    public function getOriginalDelay(): int;

    public function getConstructorArgs(): array;

    public function isLocked(): bool;
}
