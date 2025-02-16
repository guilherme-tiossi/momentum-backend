<?php

namespace App\Transformers;

use App\Models\Task;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['user', 'parent'];

    public function transform(Task $task)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'date' => $task->date ? $task->date->format('Y-m-d') : null,
            'finished' => $task->finished,
            'level' => $task->level,
            'subtasks' => $task->subtasks->isEmpty()
                ? []
                : $task->subtasks->map(fn($subtask) => (new TaskTransformer())->transform($subtask))->all(),
        ];
    }

    public function includeUser(Task $task)
    {
        return $this->item($task->user, new UserTransformer(), 'users');
    }

    public function includeParent(Task $task)
    {
        if ($task->parent) {
            return $this->item($task->parent, new TaskTransformer(), 'parent_tasks');
        }
    }
}
