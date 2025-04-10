<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskListTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function returns_tasks_in_tree_structure_by_default()
    {
        $parentTask = Task::factory()->create(['user_id' => $this->user->id, 'level' => 0, 'date' => null]);
        $subTask = Task::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parentTask->id, 'level' => 1, 'date' => null]);

        $response = $this->getJson('/api/tasks?level=0');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $parentTask->id)
            ->assertJsonPath('data.0.attributes.subtasks.0.id', $subTask->id);
    }

    /** @test */
    public function returns_daily_tasks_weekdays()
    {
        $weekdayTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
        ]);

        $weekendTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
        ]);

        // ensures that date is always weekday
        $response = $this->getJson('/api/tasks?date=' . now()->addDays((8 - now()->dayOfWeek) % 7)->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $weekdayTask->id)
            ->assertJsonPath('data.1.id', (string) $weekendTask->id);
    }
}
