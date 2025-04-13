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
        $parentTask = Task::factory()->create(['user_id' => $this->user->id, 'level' => 0]);
        $subTask = Task::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parentTask->id, 'level' => 1]);

        $response = $this->getJson('/api/tasks?level=0');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $parentTask->id)
            ->assertJsonPath('data.0.attributes.subtasks.0.id', $subTask->id);
    }
}
