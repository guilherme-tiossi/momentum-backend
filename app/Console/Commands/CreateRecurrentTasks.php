<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\RecurrentTask;
use Illuminate\Console\Command;

class CreateRecurrentTasks extends Command
{
    protected $signature = 'createRecurrentTask';

    protected $description = 'Creates tasks from recurrentTask blueprints.';

    public function handle()
    {
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek;

        $recurrentTasks = RecurrentTask::with('subtasks')
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        $subtasksToInsert = [];
        foreach ($recurrentTasks as $recurrentTask) {
            $shouldCreate = false;
            switch ($recurrentTask->recurrency_type) {
                case 'daily':
                    $shouldCreate = true;
                    break;

                case 'weekly':
                    $shouldCreate = $dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY;
                    break;

                case 'custom':
                    $shouldCreate = is_array($recurrentTask->days_of_week) &&
                        in_array($dayOfWeek, $recurrentTask->days_of_week);
                    break;

                default:
                    continue 2;
            }

            if (!$shouldCreate) {
                continue;
            }


            $task = Task::create([
                'user_id' => $recurrentTask->user_id,
                'recurrent_task_id' => $recurrentTask->id,
                'title' => $recurrentTask->title,
                'description' => $recurrentTask->description,
                'level' => $recurrentTask->level,
                'date' => $today,
                'finished' => false,
            ]);

            foreach ($recurrentTask->subtasks as $subtask) {
                $subtasksToInsert[] = [
                    'user_id' => $subtask->user_id,
                    'recurrent_task_id' => $subtask->id,
                    'parent_id' => $task->id,
                    'title' => $subtask->title,
                    'description' => $subtask->description,
                    'level' => $subtask->level,
                    'date' => $today,
                    'finished' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($subtasksToInsert, 300) as $tasks) {
            Task::insert($tasks);
        }
    }
}
