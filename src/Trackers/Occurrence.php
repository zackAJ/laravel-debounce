<?php

namespace Zackaj\LaravelDebounce\Trackers;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\HeaderBag;

class Occurrence
{
    /**
     * Occurrence registers a debounce hit
     */
    public function __construct(
        public Carbon $happenedAt,
        public HeaderBag $requestHeaders,
        public string $ip,
        public array $ips,
        public ?Authenticatable $user = null,
        private Collection $data = new Collection //not used for now
    ) {}

    public function headers(): HeaderBag
    {
        return $this->requestHeaders;
    }
}
