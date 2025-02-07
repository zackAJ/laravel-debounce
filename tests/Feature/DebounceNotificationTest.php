<?php

namespace Zackaj\LaravelDebounce\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\Factories\UserFactory;
use Zackaj\LaravelDebounce\DebounceNotification;
use Zackaj\LaravelDebounce\Facades\Debounce;
use Zackaj\LaravelDebounce\Tests\BaseCase;

class DebounceNotificationTest extends BaseCase
{
    use RefreshDatabase;

    public function test_normal_notification_is_debounced()
    {
        Queue::fake();
        $notif = new NormalNotification;
        $user = UserFactory::new();

        (Debounce::notification($user, $notif, 5, 'key', false))->handle();
        (Debounce::notification($user, $notif, 5, 'key', false))->handle();

        Queue::assertCount(1);
    }

    public function test_debounce_notification_is_debounced()
    {
        Queue::fake();
        $notif = new DNotification;
        $user = UserFactory::new()->create();

        (Debounce::notification($user, $notif, 5, 'key', false))->handle();
        (Debounce::notification($user, $notif, 5, 'key', false))->handle();

        Queue::assertCount(1);

    }

    public function test_normal_notification_is_fired()
    {
        FacadesNotification::fake();
        $notif = new NormalNotification;
        $user = UserFactory::new()->create();

        Debounce::notification($user, $notif, 0, 'key', true);

        FacadesNotification::assertCount(1);
    }

    public function test_debounce_notification_is_fired()
    {
        FacadesNotification::fake();
        $notif = new DNotification;
        $user = UserFactory::new()->create();

        Debounce::notification($user, $notif, 0, 'key', true);

        FacadesNotification::assertCount(1);
    }

    public function test_before_and_after_hooks_are_fired()
    {
        FacadesNotification::fake();
        $notifications = [new DNotificationAfter, new DNotificationBefore];
        $user = UserFactory::new()->create();

        foreach ($notifications as $notif) {
            Debounce::notification($user, $notif, 0, 'key', false);

            $this->assertTrue(DNotificationAfter::$fired);
        }
    }

    public function test_debounce_notification_latest_activity_is_called()
    {
        FacadesNotification::fake();
        $notif = new DNotificationLatestActivity;
        $user = UserFactory::new()->create();

        Debounce::notification($user, $notif, 0, 'key', false);

        $this->assertTrue(DNotificationLatestActivity::$delay === 1);
    }
}

class NormalNotification extends Notification implements ShouldQueue
{
    public function via(): array
    {
        return ['database'];
    }

    public function toArray()
    {
        return [];
    }
}

class DNotification extends DebounceNotification implements ShouldQueue
{
    public function via(): array
    {
        return ['database'];
    }
}

class DNotificationAfter extends DNotification
{
    public static bool $fired = false;

    public function after($notifiables): void
    {
        static::$fired = true;
    }
}

class DNotificationBefore extends DNotification
{
    public static bool $fired = false;

    public function before($notifiables): void
    {
        static::$fired = true;
    }
}

class DNotificationLatestActivity extends DNotification
{
    public static bool $fired = false;

    public static $delay = 0;

    public function getLastActivityTimestamp(mixed $notifiables): ?Carbon
    {
        static::$delay = 1;

        return null;
    }
}
