<?php

namespace Zackaj\LaravelDebounce\Commands;

use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:debounce-command')]
class DebounceConsoleMakeCommand extends ConsoleMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:debounce-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Artisan debounce command';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $relativePath = '/stubs/debounceConsole.stub';

        return __DIR__.$relativePath;
    }
}
