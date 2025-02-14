<?php

namespace App\Transformers;

use App\Models\Task;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected static array $processedTasks = [];
    protected bool $flatMode = false;

    protected array $availableIncludes = ['user'];
    protected array $defaultIncludes = ['parent', 'subtasks'];

    public function __construct(bool $flatMode = false)
    {
        $this->flatMode = $flatMode;
    }

    public function transform(Task $task)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'date' => $task->date ? $task->date->format('Y-m-d') : null,
            'finished' => $task->finished,
            'level' => $task->level,
        ];
    }

    public function includeUser(Task $task)
    {
        return $this->item($task->user, new UserTransformer(), 'users');
    }

    public function includeParent(Task $task)
    {
        if ($this->flatMode || !$task->parent || $this->isAlreadyProcessed($task->parent->id)) {
            return null;
        }

        return $this->item($task->parent, new TaskTransformer($this->flatMode), 'parent_tasks');
    }

    public function includeSubtasks(Task $task)
    {
        if ($this->flatMode || $this->isAlreadyProcessed($task->id)) {
            return null;
        }

        return $this->collection($task->subtasks, new TaskTransformer($this->flatMode), 'sub_tasks');
    }

    private function isAlreadyProcessed(int $taskId): bool
    {
        if (in_array($taskId, self::$processedTasks)) {
            return true;
        }
        self::$processedTasks[] = $taskId;
        return false;
    }
}
