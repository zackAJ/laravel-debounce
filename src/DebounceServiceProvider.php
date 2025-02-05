<?php

namespace Zackaj\LaravelDebounce;

use Illuminate\Support\ServiceProvider;
use Zackaj\LaravelDebounce\Commands\DebounceConsoleCommand;
use Zackaj\LaravelDebounce\Commands\DebounceConsoleMakeCommand;
use Zackaj\LaravelDebounce\Commands\DebounceJobMakeCommand;
use Zackaj\LaravelDebounce\Commands\DebounceNotificationMakeCommand;
use Zackaj\LaravelDebounce\Contracts\Trackable;
use Zackaj\LaravelDebounce\Managers\TrackerManager;
use Zackaj\LaravelDebounce\Trackers\Report;

class DebounceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/debounce.php', 'debounce');
    }

    public function boot()
    {
        $this->commands([
            DebounceConsoleCommand::class,
            DebounceNotificationMakeCommand::class,
            DebounceJobMakeCommand::class,
            DebounceConsoleMakeCommand::class,
        ]);

        $this->app->bind(Debouncer::class, Debouncer::class);

        $this->app->bind(Report::class, fn () => (new Report(collect([]))));

        // create with default driver config driver
        $this->app->bind(Trackable::class, fn () => TrackerManager::make()->tracker);
    }
}
