<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_lists_all_posts()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'attributes' => [
                            'text',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_creates_a_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'posts',
                'attributes' => [
                    'text' => $this->faker->sentence,
                ],
            ],
        ];

        $response = $this->postJson('/api/posts', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertDatabaseHas('posts', [
            'text' => $payload['data']['attributes']['text'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_shows_a_specific_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure($this->getJsonApiStructure());
    }

    /** @test */
    public function it_updates_a_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $payload = [
            'data' => [
                'type' => 'posts',
                'attributes' => [
                    'text' => 'Updated post text',
                ],
            ],
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'text' => 'Updated post text',
        ]);
    }

    /** @test */
    public function it_deletes_a_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id, 'deleted_at' => null]);
    }

    /** @test */
    public function it_prevents_deleting_a_post_owned_by_another_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'You cannot do this.']);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    private function getJsonApiStructure()
    {
        return [
            'data' => [
                'id',
                'attributes' => [
                    'text',
                    'created_at',
                    'updated_at'
                ]
            ],
        ];
    }
}
