<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Events\CreatedTask;
use App\Models\RecurrentTask;

class RecurrentTaskService
{
    public function listRecurrentTasks()
    {
        $dateParam = request()->date;
        $date = $dateParam ? Carbon::parse($dateParam) : null;

        $query = RecurrentTask::with(['parent', 'subtasks', 'user'])
            ->byUser(Auth::id())
            ->activeOnDate($date);

        return $query->get();
    }

    public function createRecurrentTask(array $data)
    {
        $recurrentTask = RecurrentTask::make($data['data']['attributes']);

        $recurrentTask->user()->associate(Auth::id());
        if (isset($data['data']['relationships']['task'])) {
            $recurrentTask->parent()->associate($data['data']['relationships']['recurrent_task']['data']['id']);

            $recurrentTask->level = $recurrentTask->parent->level + 1;
        }

        $recurrentTask->save();

        $recurrentStartDate = $recurrentTask->start_date ? $recurrentTask->start_date->format('Y-m-d') : null;
        $recurrentEndDate = $recurrentTask->end_date ? $recurrentTask->end_date->format('Y-m-d') : null;
        $today = Carbon::now()->format('Y-m-d');
        if ((!$recurrentStartDate && !$recurrentEndDate) || ($recurrentStartDate < $today && $recurrentEndDate > $today)) {
            $taskService = new TaskService();
            $taskData = [
                'data' => [
                    'attributes' => [
                        'title' => $recurrentTask->title,
                        'description' => $recurrentTask->description,
                        'date' => $today,
                        'finished' => false
                    ]
                ]
            ];

            if ($recurrentTask->parent_id) {
                $taskData['data']['relationships']['task']['data']['id'] = $recurrentTask->parent_id;
            }

            $task = $taskService->createTask($taskData);
            event(new CreatedTask($task));
        }

        return $recurrentTask;
    }

    public function updateRecurrentTask(array $data, RecurrentTask $recurrentTask)
    {
        $recurrentTask->fill($data['data']['attributes']);

        if (isset($data['data']['relationships']['task'])) {
            $recurrentTask->parent()->associate($data['data']['relationships']['recurrent_task']['data']['id']);
        }

        $recurrentTask->save();

        return $recurrentTask;
    }

    public function deleteRecurrentTask(RecurrentTask $recurrentTask)
    {
        $recurrentTasksToDelete = $this->getAllSubTasks($recurrentTask);
        $recurrentTasksToDelete[] = $recurrentTask;

        $recurrentTaskIds = array_map(fn($t) => $t->id, $recurrentTasksToDelete);

        foreach (array_chunk($recurrentTaskIds, 300) as $chunk) {
            RecurrentTask::whereIn('id', $chunk)->delete();
        }
    }

    private function getAllSubTasks(RecurrentTask $recurrentTask)
    {
        $recurrentTasks = [];

        foreach ($recurrentTask->subtasks as $subtask) {
            $recurrentTasks[] = $subtask;
            $recurrentTasks = array_merge($recurrentTasks, $this->getAllSubTasks($subtask));
        }

        return $recurrentTasks;
    }
}
