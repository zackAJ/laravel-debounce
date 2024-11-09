![logo](https://github.com/user-attachments/assets/b30c65c0-f28b-41c9-a231-ad46e6699c8b)

# Laravel debounce  
_by zackaj_


a laravel package that uses UniqueJobs (atomic locks) and caching to ,run only one instance of a task in a debounced interval of x seconds delay.

It allows you to debounce jobs, notifications and artisan commands out of the box.




## Features

- Debounce Notifications
- Debounce Jobs
- Debounce Artisan Commands


## Basic demo

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

using composer

```bash
  composer require zackaj/laravel-debounce
```
    

## Contributing

Contributions are always welcome!

See `contributing.md` for ways to get started.

Please adhere to this project's `code of conduct`.


## License

[MIT](https://choosealicense.com/licenses/mit/)


