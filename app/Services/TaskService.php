<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\Task;
use App\Events\CreatedTask;
use App\Events\FinishedTask;

class TaskService
{
    public function listTasks()
    {
        $dateParam = request()->date;
        $levelParam = request()->level;
        $finishedParam = request()->finished;

        $query = Task::with(['parent', 'subtasks', 'user'])
            ->byUser(Auth::id())
            ->byLevel($levelParam)
            ->byFinished($finishedParam)
            ->byDate($dateParam);

        return $query->get();
    }

    public function createTask(array $data)
    {
        $task = Task::make($data['data']['attributes']);

        $task->user()->associate(Auth::id());
        if (isset($data['data']['relationships']['task'])) {
            $task->parent()->associate($data['data']['relationships']['task']['data']['id']);

            $task->level = $task->parent->level + 1;
        }

        $task->save();

        event(new CreatedTask($task));

        return $task;
    }

    public function updateTask(array $data, Task $task)
    {
        $initial_status = $task->finished;

        $task->fill($data['data']['attributes']);

        if (isset($data['data']['relationships']['task'])) {
            $task->parent()->associate($data['data']['relationships']['task']['data']['id']);
        }

        $task->save();

        if ($task->finished == true && $initial_status == false) {
            $tasksToUpdate = $this->getAllUnfinishedSubTasks($task);

            $taskIds = array_map(fn($t) => $t->id, $tasksToUpdate);

            foreach (array_chunk($taskIds, 300) as $chunk) {
                Task::whereIn('id', $chunk)->update(['finished' => true]);
            }

            event(new FinishedTask($task));
        }

        return $task;
    }

    public function deleteTask(Task $task)
    {
        $tasksToDelete = $this->getAllSubTasks($task);
        $tasksToDelete[] = $task;

        $taskIds = array_map(fn($t) => $t->id, $tasksToDelete);

        foreach (array_chunk($taskIds, 300) as $chunk) {
            Task::whereIn('id', $chunk)->delete();
        }
    }

    private function getAllSubTasks(Task $task)
    {
        $tasks = [];

        foreach ($task->subtasks as $subtask) {
            $tasks[] = $subtask;
            $tasks = array_merge($tasks, $this->getAllSubTasks($subtask));
        }

        return $tasks;
    }

    private function getAllUnfinishedSubTasks(Task $task)
    {
        $tasks = [];

        foreach ($task->subtasks as $subtask) {
            if ($subtask->finished) continue;
            $tasks[] = $subtask;
            $tasks = array_merge($tasks, $this->getAllSubTasks($subtask));
        }

        return $tasks;
    }

    public function getTaskReport()
    {
        $today = Carbon::now();

        $dailyTasks = Task::byUser(Auth::id())
            ->whereBetween('date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->get();

        $weeklyTasks = Task::byUser(Auth::id())
            ->whereBetween('date', [$today->copy()->subDays(7), $today->copy()->endOfWeek()])
            ->get();

        $dailyFinished = $dailyTasks->where('finished', true)->count();
        $weeklyFinished = $weeklyTasks->where('finished', true)->count();

        return ['data' => [
            'daily' => [
                'finished' => $dailyFinished,
                'total' => $dailyTasks->count(),
            ],
            'weekly' => [
                'finished' => $weeklyFinished,
                'total' => $weeklyTasks->count(),
            ],
        ]];
    }
}
