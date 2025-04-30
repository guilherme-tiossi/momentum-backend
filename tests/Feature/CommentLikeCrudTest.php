<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentLikeCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_lists_all_comment_likes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likes = CommentLike::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/comment_likes');

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
    public function it_creates_a_comment_like()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        $this->actingAs($user);

        $payload = [
            'data' => [
                'type' => 'likes',
                'relationships' => [
                    'comment' => ['data' => ['id' => $comment->id]]
                ],
            ],
        ];

        $response = $this->postJson('/api/comment_likes', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertDatabaseHas('comment_likes', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
        ]);
    }

    /** @test */
    public function it_deletes_a_comment_like()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $like = CommentLike::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/comment_likes/{$like->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comment_likes', ['id' => $like->id]);
    }

    /** @test */
    public function it_prevents_deleting_a_comment_like_owned_by_another_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $like = CommentLike::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/comment_likes/{$like->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'You cannot do this.']);

        $this->assertDatabaseHas('comment_likes', ['id' => $like->id]);
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
