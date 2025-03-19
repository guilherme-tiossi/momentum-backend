<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_lists_all_likes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likes = Like::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/likes');

        $response->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => [
                            'id',
                            'attributes' => [
                                'timestamp'
                            ]
                        ]
                    ],
                ]
            );
    }

    /** @test */
    public function it_creates_a_like()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'likes',
                'relationships' => [
                    'post' => ['data' => ['id' => $post->id]]
                ],
            ],
        ];

        $response = $this->postJson('/api/likes', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /** @test */
    public function it_deletes_a_like()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $like = Like::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/likes/{$like->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('likes', ['id' => $like->id]);
    }

    /** @test */
    public function it_prevents_deleting_a_like_owned_by_another_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/likes/{$like->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'You cannot do this.']);

        $this->assertDatabaseHas('likes', ['id' => $like->id]);
    }

    private function getJsonApiStructure()
    {
        return [
            'data' => [
                'id',
                'attributes' => [
                    'timestamp'
                ]
            ],
        ];
    }
}
