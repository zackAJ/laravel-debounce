<?php

namespace Zackaj\LaravelDebounce\Debouncers;

use Carbon\Carbon;

class JobDebouncer extends TrackerDebouncer
{
    /**
     * NOTE: $job variable already exists that's why I named it $queuable
     *
     * @param  \Zackaj\LaravelDebounce\Jobs\DebounceJob|mixin  $queuable
     */
    public function __construct(
        public $queuable,
        public $delay,
        public string $uniqueId,
        public bool $sync = false
    ) {
        parent::__construct($delay);
    }

    public function execute(): void
    {
        if ($this->isDebounceable($this->queuable)) {
            $this->queuable->setReport($this->getReport());
        }

        if ($this->sync) {
            dispatch_sync($this->queuable);
        } else {
            dispatch($this->queuable);
        }
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
    }

    public function before()
    {
        if ($this->isDebounceable($this->queuable)) {
            $this->queuable->before();
        }
    }

    public function after(): void
    {
        if ($this->isDebounceable($this->queuable)) {
            $this->queuable->after();
        }
    }

    public function getOriginalDelay(): int
    {
        return $this->originalDelay;
    }

    public function getLastActivityTimestamp(): ?Carbon
    {
        if (! $this->isDebounceable($this->queuable)) {
            return parent::getLastActivityTimestamp();
        }

        return
            $this->queuable->getLastActivityTimestamp() ??
            parent::getLastActivityTimestamp();
    }
}
