<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function creates_user_and_returns_transformed_data()
    {
        $data = [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'email' => 'john@example.com',
                    'password' => 'secret123',
                    'pfp' => 1,
                    'header' => 'header.jpg',
                    'uses_default_pfp' => true,
                    'uses_default_header' => false,
                ],
            ],
        ];

        $response = $this->json('POST', '/api/create-user', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure($this->getJsonApiStructure());
    }

    /** @test */
    public function updates_user_and_returns_transformed_data()
    {
        $user = User::factory()->create();

        $payload = [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Updated Name',
                    'username' => 'updatedusername',
                    'email' => 'updated@example.com',
                ]
            ],
        ];

        $this->actingAs($user);

        $response = $this->json('PATCH', "/api/users/{$user->id}", $payload);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Updated Name',
            'username' => 'updatedusername',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function shows_user_and_returns_transformed_data()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->json('GET', "/api/users/{$user->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure($this->getJsonApiStructure());
    }

    /** @test */
    public function allows_deletion_only_for_authenticated_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);
        $response = $this->json('DELETE', "/api/users/{$user2->id}");
        $response->assertStatus(403);

        $this->actingAs($user2);
        $response = $this->json('DELETE', "/api/users/{$user2->id}");
        $response->assertStatus(204);
    }

    private function getJsonApiStructure()
    {
        return [
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'name',
                    'email',
                    'username',
                    'pfp',
                    'header',
                    'uses_default_pfp',
                    'uses_default_header',
                    'streak'
                ]
            ]
        ];
    }
}
