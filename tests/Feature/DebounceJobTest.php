<?php

namespace Tests\Feature;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Zackaj\LaravelDebounce\DebounceJob;
use Zackaj\LaravelDebounce\Facades\Debounce;
use Zackaj\LaravelDebounce\Tests\BaseCase;

class DebounceJobTest extends BaseCase
{
    use RefreshDatabase;

    public function test_normal_job_is_debounced()
    {
        Queue::fake();
        $job = new NormalJob;

        (Debounce::job($job, 5, 'key', false))->handle();
        (Debounce::job($job, 5, 'key', false))->handle();

        Queue::assertCount(1);
    }

    public function test_debounce_job_is_debounced()
    {
        Queue::fake();
        $job = new DJob;

        (Debounce::job($job, 5, 'key', false))->handle();
        (Debounce::job($job, 5, 'key', false))->handle();

        Queue::assertCount(1);
    }

    public function test_normal_job_is_fired()
    {
        $job = new NormalJob;

        Debounce::job($job, 0, 'key', false);

        $this->assertTrue(NormalJob::$fired);
    }

    public function test_debounce_job_is_fired()
    {
        $job = new DJob;

        Debounce::job($job, 0, 'key', false);

        $this->assertTrue(DJob::$fired);
    }
}

class NormalJob implements ShouldQueue
{
    public static bool $fired = false;

    public function handle(): void
    {
        static::$fired = true;
    }
}

class DJob extends DebounceJob
{
    public static bool $fired = false;

    public function handle(): void
    {
        static::$fired = true;
    }
}
