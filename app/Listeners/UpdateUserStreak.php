<?php

namespace App\Listeners;

use Carbon\Carbon;
use App\Events\FinishedTask;
use App\Services\UserService;

class UpdateUserStreak
{
    public function handle(FinishedTask $event)
    {
        $now = Carbon::now();
        $user = $event->task->user;
        $streak = $user->streak ?? 0;

        if (!$user->last_finished_task || $user->last_finished_task->diffInDays($now) === 1) {
            $streak++;
            $data = ['attributes' => ['last_finished_task' => $now, 'streak' => $streak]];
            app(UserService::class)->updateUser($data, $user);
        }
    }
}
