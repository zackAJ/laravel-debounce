![logo](https://github.com/user-attachments/assets/b30c65c0-f28b-41c9-a231-ad46e6699c8b)

# Laravel debounce  
_by zackaj_

Laravel-debounce allows you to accumulate / debounce a job, notification or command to avoid spamming your users and your app's queue.

It also tracks and registers every request occurrence and gives you a nice [report tracking](#report-tracking) with information like `ip address` and `authenticated user` per request.


# Table of Contents

- [Introduction](#introduction)
  - [Features](#features)
  - [Demo](#demo)

- [Installation](#installation)
  - [Prerequisites](#prerequisites)
  - [Composer](#composer)

- [Usage](#usage)
  - [Basic usage](#basic-usage)
  - [Advanced usage](#advanced-usage)
    - [Make commands](#make-commands)
    - [No facade usage](#no-facade-usage)
    - [Report Tracking](#report-tracking)
    - [Before After Hooks](#before-after-hooks)
    - [Override Timestamp](#override-timestamp)

- [Bonus CLI Debounce](#bonus-cli-debounce)
- [Debugging And Monitoring](#debugging-and-monitoring)
- [Known Issues](#known-issues)
- [Contributing](#contributing)
- [License](#license)

## Introduction

This laravel package uses UniqueJobs (atomic locks) and caching to run only one instance of a task in a debounced interval of x seconds delay.

Everytime a new activity is recorded (occurrence), the execution is delayed by x seconds.

### Features

- Debounce Notifications, Jobs and Artisan Commands [Basic usage](#basic-usage) & [Advanced usage](#advanced-usage)
- [Report Tracking](#report-tracking)
- [Bonus CLI Debounce](#bonus-cli-debounce)


### Demo

A debounced notification to bulk notify users about new uploaded files.

https://github.com/user-attachments/assets/b1d5aafd-256d-4f6f-b31a-0d6dc516793b


<details>
<summary>See Code</summary>

FileUploaded.php
```php
<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FileUploaded extends Notification
{
    use Queueable;

    public function __construct(public File $file) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'files' => $this->file->user->files()
                ->where('created_at', '>=', $this->file->created_at)
                ->get(),
        ];
    }
}

```

DemoController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Notifications\FileUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Zackaj\LaravelDebounce\Facades\Debounce;

class DemoController extends Controller
{
    public function normalNotification(Request $request)
    {
        $user = $request->user();
        $file = File::factory()->create(['user_id' => $user->id]);
        $otherUsers = User::query()->whereNot('id', $user->id)->get();

        Notification::send($otherUsers, new FileUploaded($file));

        return back();
    }

    public function debounceNotification(Request $request)
    {
        $user = $request->user();
        $file = File::factory()->create(['user_id' => $user->id]);
        $otherUsers = User::query()->whereNot('id', $user->id)->get();

        Debounce::notification(
            notifiables: $otherUsers,
            notification:new FileUploaded($file),
            delay: 5,
            uniqueKey:$user->id,
        );

        return back();
    }
}
```
</details>

## Installation

### Prerequisites
- Laravel application (> 10.x)
- Up and running cache system that supports [atomic locks](https://laravel.com/docs/11.x/cache#atomic-locks)
- Up and running [queue worker](https://laravel.com/docs/11.x/queues)

### Composer

```bash
  composer require zackaj/laravel-debounce
```

## Usage

### Basic usage
You can debounce existing jobs, notifications and commands with zero setup.

**Warning** you can't access [report tracking](#report-tracking) without extending the package's classes, see [Advanced usage](#advanced-usage).

```php
use Zackaj\LaravelDebounce\Facades\Debounce;


//job
Debounce::job(
    job:new Job(),//replace
    delay:5,//delay in seconds
    uniqueKey:auth()->user()->id,//debounce per Job class name + uniqueKey
    sync:false, //optional, job will be fired to the queue
);

//notification
Debounce::notification(
    notifiables: auth()->user(),
    notification: new Notification(),//replace
    delay: 5,
    uniqueKey: auth()->user()->id,
    sendNow: false,
);

//command
Debounce::command(
    command: new Command(),//replace
    delay: 5,
    uniqueKey: $request->ip(),
    parameters: ['name' => 'zackaj'],//see Artisan::call() signature
    toQueue: false,//optional, send command to the queue when executed
    outputBuffer: null,//optional, //see Artisan::call() signature
);
```

## Advanced usage
In order to use:
- [No Facade Usage](#no-facade-usage)
- [Report Tracking](#report-tracking)
- [before/after Hooks](#before-after-hooks)
- [Debounce from custom timestamp](#override-timestamp)

your existing jobs, notifications and commands must extend:

```php
use Zackaj\LaravelDebounce\DebounceJob;
use Zackaj\LaravelDebounce\DebounceNotification;
use Zackaj\LaravelDebounce\DebounceCommand;
```

or just generate new ones using the available [make commands](#make-commands).

### Make commands

- Notification
```bash
php artisan make:debounce-notification TestNotification
```

- Job
```bash
php artisan make:debounce-job TestJob
```

- Command
```bash
php artisan make:debounce-command TestCommand
```

### No facade usage
Alternatively, now you can debounce from the job, notification and command instances directly without using the `Debounce facade` used in [Basic usage](#basic-usage)

```php
(new Job())->debounce(...);

(new Notification())->debounce(...);

(new Command())->debounce(...);
```

### Report Tracking
Laravel-debounce uses the cache to store every request occurrence, use `getReport()` method within your debounceables to access the report chain that has a collection of occurrences.

Every report will have one occurrence minimum.

```php
<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Zackaj\LaravelDebounce\DebounceJob;

class Jobless extends DebounceJob implements ShouldQueue
{
    use Dispatchable;

    public function handle(): void
    {
        $this->getReport()->occurrences;//collection of occurrences
        $this->getReport()->occurrences->count();
        $this->getReport()->occurrences->first()->happenedAt;
        $this->getReport()->occurrences->first()->ip;
        $this->getReport()->occurrences->first()->ips;
        $this->getReport()->occurrences->first()->requestHeaders;//HeaderBag
        $this->getReport()->occurrences->first()->user;//authenticated user | null
    }
}

```

### Before After Hooks
If you wish to run some code before and/or after firing the `debounceables` you can use the available hooks.

**Important:** `after()` hook could run before your `debounceable` is handled if it's `sent to the queue` when:
- `sendNow==false` and your notification `implements ShouldQueue`
- `sync==false` and your job `implements ShouldQueue`
- `toQueue==true` (command)

see: [Basic usage](#basic-usage)


#### Debounce job

```php
<?php
...
class Jobless extends DebounceJob implements ShouldQueue
{
...
    public function before(): void
    {
        //run before dispatching the job
    }

    public function after(): void
    {
        //run after dispatching the job
    }
}
```

#### Debounce notification
You get the `$notifiables` injected into the hooks.

```php
<?php
...

class FileUploaded extends DebounceNotification
{
...
    public function before($notifiables): void
    {
        //run before sending the notification
    }

    public function after($notifiables): void
    {
        //run after sending the notification
    }
}
```

#### Debounce command
Due to limitations, the hook methods must be `static`.

```php
<?php
...

class Test extends DebounceCommand
{
...
    public static function before(): void
    {
        //run before executing the command
    }

    public static function after(): void
    {
        //run after executing the command
    }

}

```

### Override Timestamp
By default laravel-debounce debounces from the last occurrence `happenedAt` timestamp

```php
public function getLastActivityTimestamp(): ?Carbon
{
    return $this->getReport()->occurrences->last()->happenedAt;
}
```

You can override this method in your `debounceables` in order to debounce from a custom timestamp of your choice. If `null` is returned the debouncer will fallback to the default implementation above.

#### Debounce job

```php
<?php
...
class Jobless extends DebounceJob implements ShouldQueue
{
...
    public function getLastActivityTimestamp(): ?Carbon
    {
        return Message::latest()->first()?->seen_at;
    }
}
```

#### Debounce notification
You get the `$notifiables` injected into the method.

```php
<?php
...

class FileUploaded extends DebounceNotification
{
...
    public function getLastActivityTimestamp(mixed $notifiables): ?Carbon
    {
        return $this->file->user->files->latest()->first()?->created_at;
    }
}
```

#### Debounce command
Due to limitations, the method must be `static`.

```php
<?php
...

class Test extends DebounceCommand
{
...
    public static function getLastActivityTimestamp(): ?Carbon
    {
        return User::latest()->first()?->created_at;
    }
}

```

## Bonus CLI Debounce
For fun, you can actually debounce commands from the CLI using the `debounce:command` Artisan command.

```php
php artisan debounce:command 5 uniqueKey app:test
```
here's the signature for the command:
`php artisan debounce:command {delay} {uniqueKey} {signature*}`

## Debugging And Monitoring
I recommend using [Laravel telescope](https://laravel.com/docs/11.x/telescope) to see the debouncer live in the queues tab and to debug any failures.

## Known Issues

1. Unique lock gets stuck sometimes when jobs fail [github issue](https://github.com/laravel/framework/issues/37729), I made a fix to the laravel core framework about this give it a reaction: [PR (merged)](https://github.com/laravel/framework/pull/54000) 
    - cause: this happens when deleted models are unserialized causing the job to fail without clearing the lock.
    - solution: don't use `SerializesModels` trait on Notifications/Jobs. (old temporary solution, now the bug is fixed)


## Contributing

Contributions, issues and suggestions are always welcome! See `contributing.md` for ways to get started.


## License

[MIT](https://choosealicense.com/licenses/mit/)
