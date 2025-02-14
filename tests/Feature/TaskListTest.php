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

        $response = $this->getJson('/api/tasks?flatten=false');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $parentTask->id);
        // ->assertJsonPath('included.0.id', (string) $subTask->id);
    }

    /** @test */
    public function returns_flat_list_when_flatten_is_true()
    {
        $parentTask = Task::factory()->create(['user_id' => $this->user->id, 'level' => 0]);
        $subTask = Task::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parentTask->id, 'level' => 1]);

        $response = $this->getJson('/api/tasks?flatten=true');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $parentTask->id)
            ->assertJsonPath('data.1.id', (string) $subTask->id)
            ->assertJsonMissing(['included']);
    }

    /** @test */
    public function returns_daily_tasks_weekdays()
    {
        $weekdayTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
            'includes_weekend' => false
        ]);

        $weekendTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
            'includes_weekend' => true
        ]);

        // ensures taht date is always weekday
        $response = $this->getJson('/api/tasks?date=' . now()->addDays((8 - now()->dayOfWeek) % 7)->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $weekdayTask->id)
            ->assertJsonPath('data.1.id', (string) $weekendTask->id);
    }

    /** @test */
    public function only_returns_weekend_tasks_on_weekends()
    {
        $weekdayTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
            'includes_weekend' => false
        ]);

        $weekendTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
            'includes_weekend' => true
        ]);

        $response = $this->getJson('/api/tasks?date=' . now()->nextWeekendDay()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $weekendTask->id);
    }

    /** @test */
    public function includes_weekend_tasks_when_applicable()
    {
        $weekendTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'date' => null,
            'includes_weekend' => true
        ]);

        $response = $this->getJson('/api/tasks?date=' . now()->nextWeekendDay()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', (string) $weekendTask->id);
    }
}
