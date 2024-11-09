# laravel-debounce

a laravel package that uses UniqueJobs (atomic locks) and caching to:

- make sure that you run only one instance of a task.
- combine multiple instances of an action within a debounced delay into one single action.

the best example use case is debounced notifications, to notify a user after 15min of the last activity recorded

```php
<?php

        $notif = new FileUpload($file);

        $notif
            ->debounce(
                notifiables: $request->user(),
                delay: 10,
                uniqueKey: $request->user()->id
            );
```

<!-- TODO: write docs -->
