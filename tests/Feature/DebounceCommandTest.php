<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Zackaj\LaravelDebounce\DebounceCommand;
use Zackaj\LaravelDebounce\Facades\Debounce;
use Zackaj\LaravelDebounce\Tests\BaseCase;

class DebounceCommandTest extends BaseCase
{
    use RefreshDatabase;

    public function test_normal_command_is_debounced()
    {
        Queue::fake();
        $command = new NormalCommand;
        Artisan::registerCommand($command);

        (Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false))->handle();
        (Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false))->handle();

        Queue::assertCount(1);
    }

    public function test_normal_command_is_fired()
    {
        Queue::fake();
        $command = new NormalCommand;
        Artisan::registerCommand($command);

        (Debounce::command('test:test', 0, 'key', ['word' => 'test arg'], false))->handle();

        $this->assertTrue(NormalCommand::$fired);
    }

    public function test_debounce_command_is_debounced()
    {
        Queue::fake();
        $command = new DCommand;
        Artisan::registerCommand($command);

        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();
        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();

        Queue::assertCount(1);
    }

    public function test_debounce_command_is_fired()
    {
        Queue::fake();
        $command = new DCommand;
        Artisan::registerCommand($command);

        (Debounce::command('dtest:test', 0, 'key', ['word' => 'test arg'], false))->handle();

        $this->assertTrue(DCommand::$fired);
    }
}

class NormalCommand extends Command
{
    protected $signature = 'test:test {word}';

    protected $description = 'test command';

    public static $fired = false;

    public function handle()
    {
        static::$fired = true;
    }
}

class DCommand extends DebounceCommand
{
    protected $signature = 'dtest:test {word}';

    protected $description = 'test debounce command';

    public static $fired = false;

    public function handle()
    {

        static::$fired = true;
    }
}
