<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_resets_streak_if_last_finished_task_is_more_than_one_day_ago()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
            'last_finished_task' => Carbon::now()->subWeek()->format('Y-m-d'),
            'streak' => 2,
            'last_login_date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $credentials);
        $response->assertStatus(200);

        $user->refresh();

        $this->assertNull($user->last_finished_task);
        $this->assertNull($user->streak);
    }

    /** @test */
    public function login_keeps_streak_if_last_finished_task_is_yesterday()
    {
        $user = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => 'password',
            'last_finished_task' => Carbon::yesterday()->format('Y-m-d'),
            'streak' => 2,
            'last_login_date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $credentials = [
            'email' => 'test2@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $credentials);
        $response->assertStatus(200);

        $user->refresh();

        $this->assertEquals(Carbon::yesterday()->format('Y-m-d'), $user->last_finished_task->format('Y-m-d'));
        $this->assertEquals(2, $user->streak);
    }
}
