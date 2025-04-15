<?php

namespace Tests\Feature;

use Carbon\Carbon;
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
        $parentTask = Task::factory()->create(['user_id' => $this->user->id, 'level' => 0]);
        $subTask = Task::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parentTask->id, 'level' => 1]);

        $response = $this->getJson('/api/tasks?level=0');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $parentTask->id)
            ->assertJsonPath('data.0.attributes.subtasks.0.id', $subTask->id);
    }

    /** @test */
    public function returns_correct_task_report()
    {
        $fixedNow = Carbon::parse('2024-04-14 12:00:00');
        Carbon::setTestNow($fixedNow);

        Task::factory()->create(['user_id' => $this->user->id, 'date' => $fixedNow->copy(), 'finished' => true]);
        Task::factory()->create(['user_id' => $this->user->id, 'date' => $fixedNow->copy(), 'finished' => false]);

        $weekDate = $fixedNow->copy()->startOfWeek()->addDays(1);
        Task::factory()->create(['user_id' => $this->user->id, 'date' => $weekDate, 'finished' => true]);
        Task::factory()->create(['user_id' => $this->user->id, 'date' => $fixedNow->copy()->subWeeks(2), 'finished' => true]);

        $response = $this->getJson('/api/taskReport');

        $response = $this->getJson('/api/taskReport');

        $response->assertStatus(200)
            ->assertJson(['data' => [
                'daily' => [
                    'finished' => 1,
                    'total' => 2,
                ],
                'weekly' => [
                    'finished' => 2,
                    'total' => 3,
                ]
            ]]);
    }
}
