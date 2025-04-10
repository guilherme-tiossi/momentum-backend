<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'date' => Carbon::now()->format('Y-m-d'),
            'finished' => $this->faker->boolean(20),
            'level' => 0,
        ];
    }

    /**
     * Indicate that the task is a subtask.
     */
    public function subtask(Task $parentTask)
    {
        return $this->state(function () use ($parentTask) {
            return [
                'parent_id' => $parentTask->id,
                'level' => $parentTask->level + 1,
            ];
        });
    }

    /**
     * Indicate that the task is finished.
     */
    public function finished()
    {
        return $this->state(fn() => ['finished' => true]);
    }
}
