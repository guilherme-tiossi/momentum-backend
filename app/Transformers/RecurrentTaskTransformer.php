<?php

namespace App\Transformers;

use App\Models\RecurrentTask;
use League\Fractal\TransformerAbstract;

class RecurrentTaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['user', 'parent'];

    public function transform(RecurrentTask $task)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'days_of_week' => $task->days_of_week,
            'recurrency_type' => $task->recurrency_type,
            'start_date' => $task->start_date?->format('Y-m-d'),
            'end_date' => $task->end_date?->format('Y-m-d'),
            'level' => $task->level,
            'subtasks' => $task->subtasks->isEmpty()
                ? []
                : $task->subtasks->map(fn($subtask) => (new self())->transform($subtask))->all(),
        ];
    }

    public function includeUser(RecurrentTask $task)
    {
        return $this->item($task->user, new UserTransformer(), 'users');
    }

    public function includeParent(RecurrentTask $task)
    {
        if ($task->parent) {
            return $this->item($task->parent, new self(), 'parent_recurrent_tasks');
        }
    }
}
