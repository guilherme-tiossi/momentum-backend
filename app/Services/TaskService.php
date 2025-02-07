<?php

namespace App\Services;

use Auth;
use App\Models\Task;

class TaskService
{
    public function listTasks()
    {
        return Task::with(['parent', 'subtasks', 'user'])->byUser(Auth::id())->get();
    }

    public function createTask(array $data)
    {
        $task = Task::make($data['data']['attributes']);
        
        $task->user()->associate(Auth::id());
        if (isset($data['data']['relationships']['task'])) {
            $task->parent()->associate($data['data']['relationships']['task']['data']['id']);
        }

        $task->save();

        // event(new TaskCreated($task));

        return $task;
    }

    public function updateTask(array $data, Task $task)
    {
        $task->fill($data['attributes']);
        
        if (isset($data['data']['relationships']['task'])) {
            $task->parent()->associate($data['data']['relationships']['task']['data']['id']);
        }

        $task->save();

        // event(new TaskUpdated($task));

        return $task;
    }

    public function deleteTask(Task $task)
    {
        $tasksToDelete = $this->getAllSubTasks($task);
        $tasksToDelete[] = $task;
    
        $taskIds = array_map(fn ($t) => $t->id, $tasksToDelete);
    
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
