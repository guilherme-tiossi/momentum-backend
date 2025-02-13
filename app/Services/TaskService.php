<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\Task;
use App\Events\FinishedTask;

class TaskService
{
    public function listTasks()
    {
        $dateParam = request()->date;
        $date = Carbon::parse($dateParam);
        $finishedParam = request()->finished;

        $query = Task::with(['parent', 'subtasks', 'user'])
            ->byUser(Auth::id())
            ->byDate($dateParam)
            ->byFinished($finishedParam);

        /**
         * By default, the API should return only parent tasks in the `data` field, 
         * while subtasks are included under the `included` key.
         * 
         * However, when filtering by "finished" status (completed/uncompleted tasks),
         * some subtasks might be marked as finished while their parent tasks are not.
         * In such cases, the API should return these subtasks regardless of their parent’s status.
         * 
         * To achieve this, the default filter that restricts results to only parent tasks 
         * (where `parent_id` is null) should be removed when filtering by finished status.
         */
        if (!$finishedParam) {
            $query->byParent(null);
        }

        if ($date->isWeekend()) {
            $query->includeWeekend(true);
        }

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

        // event(new TaskCreated($task));

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

        // event(new TaskDeleted($task));
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
}
