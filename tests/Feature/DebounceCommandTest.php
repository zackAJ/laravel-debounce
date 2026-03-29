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
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        Queue::fake();
        $command = new NormalCommand;
        Artisan::registerCommand($command);

        (Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false))->handle();
        (Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false))->handle();

        Queue::assertCount(1);
    }

    public function test_debounce_command_is_debounced()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        Queue::fake();
        $command = new DCommand;
        Artisan::registerCommand($command);

        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();
        (Debounce::command('dtest:test', 5, 'key', ['word' => 'test arg'], false))->handle();

        Queue::assertCount(1);
    }

    public function test_normal_command_is_fired()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        $command = new NormalCommand;
        Artisan::registerCommand($command);

        Debounce::command('test:test', 0, 'key', ['word' => 'test arg'], false);

        $this->assertTrue(NormalCommand::$fired);
    }

    public function test_debounce_command_is_fired()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        $command = new DCommand;
        Artisan::registerCommand($command);

        Debounce::command('dtest:test', 0, 'key', ['word' => 'test arg'], false);

        $this->assertTrue(DCommand::$fired);
    }

    public function test_debounce_from_cli_is_debounced_and_fired()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        Queue::fake();
        Artisan::registerCommand(new NormalCommand);
        Artisan::registerCommand(new DCommand);
        Artisan::registerCommand(new DebounceConsoleCommand);
        $commands = [
            ['signature' => 'test:test', 'class' => NormalCommand::class],
            ['signature' => 'dtest:test', 'class' => DCommand::class],
        ];

        foreach ($commands as $key => $cmd) {
            // for future tests
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

    public function test_before_and_after_hooks_are_fired()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        $commands = [new DCommandAfter, new DCommandBefore];

        foreach ($commands as $cmd) {
            Artisan::registerCommand($cmd);

            Debounce::command('dtest:test', 0, 'key', ['word' => 'test arg'], false);

            $this->assertTrue($cmd::$fired);
        }
    }

    public function test_command_debouncer_throws_error_for_laravel_version_under_10(): void
    {
        if (version_compare(app()->version(), '11.0.0', '>=')) {
            $this->markTestSkipped('Command debouncer works for laravel version >= 11');
        }

        Artisan::registerCommand(new NormalCommand);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CommandDebouncer requires Laravel version >= 11');

        Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false);
    }

    public function test_command_is_not_debounced_when_disabled()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        config(['debounce.enabled' => false]);
        Queue::fake();
        Artisan::registerCommand(new NormalCommand);

        Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], true);
        Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], true);

        Queue::assertCount(2);
    }

    public function test_command_is_fired_when_disabled()
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Command debouncer is not supported for laravel version < 11');
        }

        config(['debounce.enabled' => false]);
        Artisan::registerCommand(new NormalCommand);

        Debounce::command('test:test', 5, 'key', ['word' => 'test arg'], false);

        $this->assertTrue(NormalCommand::$fired);
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

class DCommandAfter extends DebounceCommand
{
    protected $signature = 'dtest:test {word}';

    protected $description = 'test debounce command';

    public static $fired = false;

    public static function after(): void
    {
        static::$fired = true;
    }

    public function handle() {}
}

class DCommandBefore extends DebounceCommand
{
    protected $signature = 'dtest:test {word}';

    protected $description = 'test debounce command';

    public static $fired = false;

    public static function before(): void
    {
        static::$fired = true;
    }

    public function handle() {}
}
