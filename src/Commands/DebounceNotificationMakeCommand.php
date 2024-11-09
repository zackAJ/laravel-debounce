<?php

namespace Zackaj\LaravelDebounce\Commands;

use Illuminate\Foundation\Console\NotificationMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:debounce-notification')]
class DebounceNotificationMakeCommand extends NotificationMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:debounce-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new debounce notification class';

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return __DIR__.$stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('markdown')
            ? $this->resolveStubPath('/stubs/markdown-notification.stub')
            : $this->resolveStubPath('/stubs/debounceNotification.stub');
    }
}
