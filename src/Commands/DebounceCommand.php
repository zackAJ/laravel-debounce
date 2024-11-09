<?php

namespace Zackaj\LaravelDebounce\Commands;

use Illuminate\Console\Command;
use Zackaj\LaravelDebounce\Debouncers\CommandDebouncer;

class DebounceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debounce:command {delay} {uniqueKey} {signature*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'debounce a command from the command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = $this->getCommand();
        $delay = $this->argument('delay');

        if (! is_numeric($delay)) {
            $this->fail('argument delay must be a number');
        }

        $uniqueKey = $this->argument('uniqueKey');

        $uniqueId = $this->getSignature().'-'.$uniqueKey;

        CommandDebouncer::dispatch(
            $command,
            [],
            (int) $delay,
            $uniqueId
        );
    }

    private function getCommand(): string
    {
        return implode(' ', $this->argument('signature'));
    }

    private function getSignature()
    {
        return $this->argument('signature')[0];
    }
}
