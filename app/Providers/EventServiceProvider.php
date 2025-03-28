<?php

namespace App\Providers;

use App\Events\CreatedPost;
use App\Events\CreatedTask;
use App\Events\FinishedTask;
use App\Listeners\AddPostInterests;
use App\Listeners\AddTaskInterests;
use App\Listeners\UpdateUserStreak;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FinishedTask::class => [
            UpdateUserStreak::class
        ],
        CreatedTask::class => [
            AddTaskInterests::class
        ],
        CreatedPost::class => [
            AddPostInterests::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
