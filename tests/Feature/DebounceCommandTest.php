<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Zackaj\LaravelDebounce\Commands\DebounceConsoleCommand;
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

    public function test_debounce_command_is_debounced()
    {
        Queue::fake();
        $command = new DCommand;
        Artisan::registerCommand($command);

        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();
        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();

        Queue::assertCount(1);
    }

    public function test_normal_command_is_fired()
    {
        $command = new NormalCommand;
        Artisan::registerCommand($command);

        Debounce::command('test:test', 0, 'key', ['word' => 'test arg'], false);

        $this->assertTrue(NormalCommand::$fired);
    }

    public function test_debounce_command_is_fired()
    {
        $command = new DCommand;
        Artisan::registerCommand($command);

        Debounce::command('dtest:test', 0, 'key', ['word' => 'test arg'], false);

        $this->assertTrue(DCommand::$fired);
    }

    public function test_debounce_from_cli_is_debounced_and_fired()
    {
        Queue::fake();
        Artisan::registerCommand(new NormalCommand);
        Artisan::registerCommand(new DCommand);
        Artisan::registerCommand(new DebounceConsoleCommand);
        $commands = [
            ['signature' => 'test:test', 'class' => NormalCommand::class],
            ['signature' => 'dtest:test', 'class' => DCommand::class],
        ];

        foreach ($commands as $key => $cmd) {
            //for future tests
            $args = [
                'command' => $cmd['signature'],
                'delay' => 0,
                'uniqueKey' => 'key',
                'parameters' => [
                    'word' => 'hello',
                ],
            ];

            $commandString = sprintf(
                'debounce:command %s %s %s %s',
                $args['delay'],
                $args['uniqueKey'],
                $args['command'],
                $args['parameters']['word'],
            );

            $this->artisan($commandString)->assertSuccessful();
            $this->artisan($commandString)->assertSuccessful();
            Queue::assertCount($key + 1);

            $this->assertTrue($cmd['class']::$fired);
        }
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
