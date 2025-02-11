<?php

namespace App\Transformers;

use App\Models\Task;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected static array $processedTasks = []; 

    protected array $availableIncludes = ['user'];
    protected array $defaultIncludes = ['parent', 'subtasks'];

    public function transform(Task $task)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'date' => $task->date,
            'finished' => $task->finished,
            'level' => $task->level
        ];
    }

    public function includeUser(Task $task)
    {
        return $this->item($task->user, new UserTransformer(), 'users');
    }

    public function includeParent(Task $task)
    {
        if (!$task->parent || in_array($task->parent->id, self::$processedTasks)) {
            return null;
        }

        self::$processedTasks[] = $task->parent->id;

        return $this->item($task->parent, new TaskTransformer(), 'parent_tasks');
    }

    public function includeSubtasks(Task $task)
    {
        if (in_array($task->id, self::$processedTasks)) {
            return null;
        }

        self::$processedTasks[] = $task->id;

        return $this->collection($task->subtasks, new TaskTransformer(), 'sub_tasks');
    }
}
