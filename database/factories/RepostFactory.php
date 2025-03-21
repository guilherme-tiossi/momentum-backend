<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Repost;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Repost::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'timestamp' => $this->faker->datetime()
        ];
    }
}
