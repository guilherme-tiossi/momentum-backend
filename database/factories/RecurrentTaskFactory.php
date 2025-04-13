<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\RecurrentTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurrentTaskFactory extends Factory
{
    protected $model = RecurrentTask::class;

    public function definition(): array
    {
        $recurrencyTypes = ['daily', 'weekly', 'custom'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'recurrency_type' => Arr::random($recurrencyTypes),
            'days_of_week' => $this->faker->randomElements(range(0, 6), rand(1, 3)),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->optional()->dateTimeBetween('now', '+2 months'),
            'level' => $this->faker->numberBetween(1, 5),
            'user_id' => User::factory(),
            'parent_id' => null,
        ];
    }

    public function withSubtask(RecurrentTask $parent)
    {
        return $this->state(function () use ($parent) {
            return [
                'parent_id' => $parent->id,
            ];
        });
    }
}
