<?php

namespace Zackaj\LaravelDebounce\Debouncers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Context;
use Symfony\Component\Console\Output\OutputInterface;

class CommandDebouncer extends TrackerDebouncer
{
    public function __construct(
        public string $command,
        public array $parameters,
        public $delay,
        public string $uniqueId,
        public bool $toQueue = false,
        public ?OutputInterface $outputBuffer = null
    ) {
        parent::__construct($delay);
    }

    public function execute(): void
    {
        $this->setReportByContext();

        if ($this->toQueue) {
            Artisan::queue($this->command, $this->parameters);
        } else {
            Artisan::call($this->command, [...$this->parameters], $this->outputBuffer);
        }

        $this->forgetReport();
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getOriginalDelay(): int
    {
        return $this->originalDelay;
    }

    public function before()
    {
        $concreteCommand = $this->getCommandFromSignature();

        if ($this->isDebounceable($concreteCommand)) {
            $concreteCommand::before();
        }
    }

    public function after(): void
    {
        $concreteCommand = $this->getCommandFromSignature();

        if ($this->isDebounceable($concreteCommand)) {
            $concreteCommand::after();
        }

    }

    private function getCommandFromSignature(): Command
    {
        return collect(Artisan::all())->where(fn ($cmd, $signature) => $signature === $this->getPureSignature())->firstOrFail();
    }

    private function setReportByContext(): void
    {
        Context::addHidden('report', $this->report);
    }

    private function forgetReport(): void
    {
        Context::forgetHidden('report');
    }

    /**
     * make sure command has no parameters, return only the signature
     */
    private function getPureSignature(): string
    {
        return explode(' ', $this->command)[0];
    }
}
