<?php

namespace Zackaj\LaravelDebounce\Debouncers;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Zackaj\LaravelDebounce\DebounceNotification;

class NotificationDebouncer extends TrackerDebouncer
{
    public function __construct(
        public mixed $notifiables,
        public Notification|DebounceNotification $notification,
        public $delay,
        public string $uniqueId,
        public bool $sendNow = false,
    ) {
        parent::__construct($delay);
    }

    public function execute(): void
    {
        if ($this->isDebounceable($this->notification)) {
            $this->notification->setReport($this->getReport());
        }

        if ($this->sendNow) {
            FacadesNotification::sendNow($this->notifiables, $this->notification);
        } else {
            FacadesNotification::send($this->notifiables, $this->notification);
        }
    }

    public function getLatestActivityTimestamp(): ?Carbon
    {
        if (! $this->isDebounceable($this->notification)) {
            return parent::getLatestActivityTimestamp();
        }

        return
            $this->notification->getLatestActivityTimestamp($this->notifiables) ??
            parent::getLatestActivityTimestamp();
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
    }

    public function before(): void
    {
        if ($this->isDebounceable($this->notification)) {
            $this->notification->before($this->notifiables);
        }
    }

    public function after(): void
    {
        if ($this->isDebounceable($this->notification)) {
            $this->notification->after($this->notifiables);
        }
    }

    public function getOriginalDelay(): int
    {
        return $this->originalDelay;
    }
}
