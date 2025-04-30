<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentLikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommentLike::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'comment_id' => Comment::factory(),
            'timestamp' => $this->faker->datetime()
        ];
    }
}
