<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Repost;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepostCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_lists_all_reposts()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reposts = Repost::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/reposts');

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
    public function it_creates_a_repost()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'reposts',
                'relationships' => [
                    'post' => ['data' => ['id' => $post->id]]
                ],
            ],
        ];

        $response = $this->postJson('/api/reposts', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertDatabaseHas('reposts', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /** @test */
    public function it_deletes_a_repost()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $repost = Repost::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/reposts/{$repost->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('reposts', ['id' => $repost->id]);
    }

    /** @test */
    public function it_prevents_deleting_a_repost_owned_by_another_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $repost = Repost::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/reposts/{$repost->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'You cannot do this.']);

        $this->assertDatabaseHas('reposts', ['id' => $repost->id]);
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
