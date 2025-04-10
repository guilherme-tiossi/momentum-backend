<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function completing_a_task_completes_all_its_subtasks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentTask = Task::factory()->create([
            'user_id'  => $user->id,
            'finished' => false,
            'level'    => 0,
        ]);

        $subtask1 = Task::factory()->create([
            'user_id'   => $user->id,
            'parent_id' => $parentTask->id,
            'finished'  => false,
            'level'     => 1,
        ]);

        $subtask2 = Task::factory()->create([
            'user_id'   => $user->id,
            'parent_id' => $parentTask->id,
            'finished'  => false,
            'level'     => 1,
        ]);

        $payload = [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title' => $parentTask->title,
                    'description' => $parentTask->description,
                    'date' => $parentTask->date->format('Y-m-d'),
                    'finished' => true,
                ],
            ],
        ];

        $this->assertEquals($parentTask->finished, 0);
        $this->assertEquals($subtask1->finished, 0);
        $this->assertEquals($subtask2->finished, 0);

        $response = $this->json('PUT', "/api/tasks/{$parentTask->id}", $payload);
        $response->assertStatus(200);

        $parentTask->refresh();
        $subtask1->refresh();
        $subtask2->refresh();

        $this->assertEquals($parentTask->finished, 1);
        $this->assertEquals($subtask1->finished, 1);
        $this->assertEquals($subtask2->finished, 1);
    }

    /** @test */
    public function deleting_a_task_deletes_all_its_subtasks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentTask = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $subtask = Task::factory()->create([
            'user_id'   => $user->id,
            'parent_id' => $parentTask->id,
        ]);

        $response = $this->json('DELETE', "/api/tasks/{$parentTask->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $parentTask->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('tasks', ['id' => $subtask->id, 'deleted_at' => null]);
    }

    /** @test */
    public function task_deletion_only_works_for_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser);
        $response = $this->json('DELETE', "/api/tasks/{$task->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function creating_a_subtask_sets_level_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentTask = Task::factory()->create([
            'user_id' => $user->id,
            'level'   => 1,
        ]);

        $payload = [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title'       => 'Subtask Title',
                    'description' => 'Subtask Description',
                    'date'        => Carbon::now()->format('Y-m-d'),
                    'finished'    => false,
                ],
                'relationships' => [
                    'task' => [
                        'data' => [
                            'id' => $parentTask->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->json('POST', '/api/tasks', $payload);
        $response->assertStatus(201);
        $response->assertJsonFragment(['level' => $parentTask->level + 1]);
    }

    /** @test */
    public function creating_a_task_returns_transformed_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title'             => 'Task Title',
                    'description'       => 'Task Description',
                    'date'              => Carbon::now()->format('Y-m-d'),
                    'finished'          => false,
                ],
            ],
        ];

        $response = $this->json('POST', '/api/tasks', $payload);
        $response->assertStatus(201);
        $response->assertJsonStructure($this->getJsonApiStructure());
    }

    /** @test */
    public function updating_a_task_from_unfinished_to_finished_updates_user_streak()
    {
        $user = User::factory()->create([
            'streak' => 2,
            'last_finished_task' => Carbon::yesterday()->format('Y-m-d'),
        ]);
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id'  => $user->id,
            'finished' => false,
        ]);

        $payload = [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title' => $task->title,
                    'description' => $task->description,
                    'date' => $task->date->format('Y-m-d'),
                    'finished' => true,
                ],
            ],
        ];

        $response = $this->json('PUT', "/api/tasks/{$task->id}", $payload);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(3, $user->streak);
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $user->last_finished_task->format('Y-m-d'));
    }

    /** @test */
    public function updating_a_task_with_last_finished_task_more_than_one_day_ago_does_not_increment_streak()
    {
        $user = User::factory()->create([
            'streak' => 2,
            'last_finished_task' => Carbon::now()->subDays(2)->format('Y-m-d'),
        ]);
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id'  => $user->id,
            'finished' => false,
        ]);

        $payload = [
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'title' => $task->title,
                    'description' => $task->description,
                    'date' => $task->date->format('Y-m-d'),
                    'finished' => true,
                ],
            ],
        ];

        $response = $this->json('PUT', "/api/tasks/{$task->id}", $payload);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(2, $user->streak);
        $this->assertEquals(Carbon::now()->subDays(2)->format('Y-m-d'), $user->last_finished_task->format('Y-m-d'));
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
                    'date',
                    'finished',
                    'level'
                ]
            ]
        ];
    }
}
