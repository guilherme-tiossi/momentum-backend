<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecurrentTask;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecurrentTaskCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_create_a_recurrent_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'recurrent_tasks',
                'attributes' => [
                    'title'       => 'Recurrent Task',
                    'description' => 'Do something every day',
                    'recurrency_type'   => 'daily',
                ]
            ]
        ];

        $response = $this->json('POST', '/api/recurrent_tasks', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->getJsonApiStructure());
        $this->assertDatabaseHas('recurrent_tasks', [
            'title' => 'Recurrent Task',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_list_their_recurrent_tasks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        RecurrentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        RecurrentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        RecurrentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->json('GET', '/api/recurrent_tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_view_a_recurrent_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = RecurrentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->json('GET', "/api/recurrent_tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => (string) $task->id,
            'type' => 'recurrent_tasks',
        ]);
    }

    /** @test */
    public function user_can_update_a_recurrent_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = RecurrentTask::factory()->create([
            'user_id' => $user->id,
            'title'   => 'Old Title',
        ]);

        $payload = [
            'data' => [
                'type' => 'recurrent_tasks',
                'attributes' => [
                    'title' => 'Updated Title',
                    'description' => $task->description,
                    'recurrency_type' => $task->recurrency_type,
                ]
            ]
        ];

        $response = $this->json('PUT', "/api/recurrent_tasks/{$task->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('recurrent_tasks', ['title' => 'Updated Title']);
    }

    /** @test */
    public function user_can_delete_their_recurrent_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = RecurrentTask::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->json('DELETE', "/api/recurrent_tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('recurrent_tasks', ['id' => $task->id]);
    }

    /** @test */
    public function user_cannot_delete_someone_elses_recurrent_task()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $task = RecurrentTask::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->actingAs($user2);
        $response = $this->json('DELETE', "/api/recurrent_tasks/{$task->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('recurrent_tasks', ['id' => $task->id]);
    }

    private function getJsonApiStructure()
    {
        return [
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'title',
                    'description',
                    'recurrency_type',
                ]
            ]
        ];
    }
}
