<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_timeline_posts_for_authenticated_user()
    {
        $followedUser = User::factory()->create();
        $followedUserPost = Post::factory()->create([
            'user_id' => $followedUser->id,
            'updated_at' => Carbon::parse('2025-01-01 00:00:00'),
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $userPost = Post::factory()->create([
            'user_id' => $user->id,
            'updated_at' => Carbon::parse('2024-01-01 00:00:00'),
        ]);

        $user->following()->attach($followedUser->id);

        $response = $this->getJson('api/timeline');

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

        $responseData = $response->json('data');
        $this->assertEquals($followedUserPost->id, $responseData[0]['id']);
        $this->assertEquals($userPost->id, $responseData[1]['id']);
    }
}
