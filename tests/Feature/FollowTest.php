<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FollowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        Auth::login($this->user);
    }

    /** @test */
    public function test_user_can_follow_another_user()
    {
        $response = $this->postJson("/api/users/{$this->anotherUser->id}/follow");

        $response->assertStatus(200)
            ->assertJsonStructure($this->getJsonApiStructure());

        $this->assertTrue($this->user->following->contains($this->anotherUser));
    }

    /** @test */
    public function test_user_cannot_follow_the_same_user_twice()
    {
        $this->postJson("/api/users/{$this->anotherUser->id}/follow");
        $response = $this->postJson("/api/users/{$this->anotherUser->id}/follow");

        $response->assertStatus(403)
            ->assertJson(['error' => "You are already following @{$this->anotherUser->username}."]);
    }

    /** @test */
    public function test_user_can_unfollow_another_user()
    {
        $this->user->following()->attach($this->anotherUser->id);
        $response = $this->postJson("/api/users/{$this->anotherUser->id}/unfollow");

        $response->assertStatus(204);
        $this->assertFalse($this->user->following->contains($this->anotherUser));
    }

    /** @test */
    public function test_user_can_unfollow_a_user_they_dont_follow()
    {
        $response = $this->postJson("/api/users/{$this->anotherUser->id}/unfollow");

        $response->assertStatus(204);
    }

    /** @test */
    public function test_get_followers_of_a_user()
    {
        $this->anotherUser->following()->attach($this->user->id);
        $response = $this->getJson("/api/users/{$this->user->id}/followers");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function test_get_users_a_user_is_following()
    {
        $this->user->following()->attach($this->anotherUser->id);
        $response = $this->getJson("/api/users/{$this->user->id}/following");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
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
